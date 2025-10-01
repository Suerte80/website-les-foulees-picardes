<?php

namespace App\Controller;

use App\Entity\FileItem;
use App\Enum\FileItemType;
use App\Enum\ValidateNameEnum;
use App\Repository\FileItemRepository;
use App\Service\ZipTreeExporter;
use App\Util\Sanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\isEmpty;

#[Route('/files', name: 'app_files_')]
#[IsGranted('ROLE_BUREAU')]
final class FileController extends AbstractController
{
    #[Route('/upload/{id?}', name: 'upload')]
    public function upload(?FileItem $parent, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, [
                'mapped' => false
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploaded = $form->get('file')->getData();
            $newName = uniqid() . '.' . $uploaded->guessExtension();
            $storage = $this->getParameter('files_manager_dir');

            $origName = $uploaded->getClientOriginalName();
            $mimeType = $uploaded->getClientMimeType();
            $size = $uploaded->getSize();

            $uploaded->move($storage, $newName);

            $file = new FileItem();
            $file->setName($origName);
            $file->setPath($newName);
            $file->setMimeType($mimeType);
            $file->setSize($size);
            $file->setUploadedBy($this->getUser());

            if(isset($parent))
                $file->setParent($parent);

            $entityManager->persist($file);
            $entityManager->flush();

            return $this->redirectToRoute('app_files_list');
        }

        return $this->render('file/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/', name: 'list')]
    public function list(FileItemRepository $repo): Response
    {
        $depth = 3;

        return $this->render('file/list.html.twig', [
            'files' => $repo->findRootsWithChildren($depth),
            'parent' => null,
            'depth' => $depth,
            'prev_dir_id' => null,
        ]);
    }

    #[Route('/{id}', name: 'list_dir')]
    public function listSubDir(FileItem $fileItem, FileItemRepository $repo): Response
    {
        // Vérification que c'est bien un dossier
        if($fileItem->isFolder()){
            return $this->render('file/list.html.twig', [
                'files' => $fileItem->getChildren(),
                'parent' => $fileItem,
                'depth' => 1,
                'prev_dir_id' => ($fileItem->getParent()!=null)? $fileItem->getParent()->getId(): null,
            ]);
        }

        $children = $fileItem->getChildren();
        return $this->render('file/list.html.twig', [
            'files' => $children,
            'depth' => 1,
            'prev_dir_id' => null,
        ]);
    }

    #[Route('/download/{id}', name: 'download')]
    public function download(FileItem $fileItem, ZipTreeExporter $exporter): Response
    {
        switch($fileItem->getType()) {
            case FileItemType::FILE:
                $path = $this->getParameter('files_manager_dir') . '/' . $fileItem->getPath();

                return $this->file($path, $fileItem->getName());
                break;

            case FileItemType::DIRECTORY:
                $tmp = tempnam(sys_get_temp_dir(), 'zip_');
                $zipPath = $tmp.'.zip';
                @unlink($tmp);

                // Avec une profondeur max
                $exporter->export($fileItem, $zipPath, maxDepth: 3);

                $response = new BinaryFileResponse($zipPath);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    sprintf('%s.zip', preg_replace('~\s+~', '_', $fileItem->getName() ?: 'dossier'))
                );
                // Symfony supprimera le fichier après envoi
                $response->deleteFileAfterSend(true);
                return $response;
                break;
        }
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(FileItem $fileItem, EntityManagerInterface $entityManager): Response
    {
        $haveParent = $fileItem->getParent() != null;
        if($haveParent) $parentId = $fileItem->getParent()->getId() ?? '';

        if($fileItem->isFile()){
            $path = $this->getParameter('files_manager_dir') . '/' . $fileItem->getPath();

            if(is_file($path)) {
                unlink($path);
            }
        } else if($fileItem->isFolder()){
            $this->removeFileItemChildren($fileItem, $entityManager);
        }

        $entityManager->remove($fileItem);
        $entityManager->flush();

        if($haveParent){
            return $this->redirectToRoute('app_files_list_dir', [
                'id' => $parentId
            ]);
        } else{
            return $this->redirectToRoute('app_files_list');
        }
    }

    // Create directory
    #[Route('/create/directory/', name: 'create_directory', methods: ['POST'])]
    public function createDirectory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $name = $request->request->get('name');
        $parentId = $request->request->get('parent_id');

        if(isEmpty($name)){
            return $this->json([
                'status' => 'error',
                'message' => 'Please enter a name'
            ]);
        }

        if($parentId != null){
            $parent = $entityManager->getRepository(FileItem::class)->find($parentId);
        }

        $directory = new FileItem();
        $directory->setName($name);
        $directory->setParent($parent ?? null);
        $directory->setUploadedBy($this->getUser());
        $directory->setType(FileItemType::DIRECTORY);
        $directory->setSize(0);
        $directory->setPath('');
        $directory->setMimeType('');

        $entityManager->persist($directory);
        $entityManager->flush();

        return $this->json(['status' => 'ok', 'id' => $directory->getId()]);
    }

    #[Route('/rename/{id}', name: 'rename', methods: ['POST'])]
    public function renameFile(FileItem $fileItem, Request $request, FileItemRepository $itemRepository, EntityManagerInterface $entityManager): Response
    {
        $newName = trim(Sanitizer::sanitizeName($request->request->get('newName')));

        // Vérification basique
        $validationName = Sanitizer::validateName($newName);
        if($validationName != ValidateNameEnum::SUCCESS)
            return $this->json([
                'status' => $validationName->getStatusMessage(),
                'message' => $validationName->getMessage()
            ], $validationName->getStatus());

        // Si rien n'a changé rien à faire.
        if($newName === $fileItem->getName()) {
            return $this->json([
                'status' => 'ok',
                'id' => $fileItem->getId(),
                'name' => $newName
            ]);
        }

        $dup = $itemRepository->findOneBy([
            'parent' => $fileItem->getParent(),
            'name' => $newName
        ]);
        if($dup && $dup->getId() !== $fileItem->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Un élément porte déjà ce nom.'
            ]);
        }

        $fileItem->setName($newName);

        $entityManager->flush();

        return $this->json([
            'status' => 'ok',
            'id' => $fileItem->getId(),
            'name' => $fileItem->getName()
        ]);
    }

    private function removeFileItemChildren(FileItem $fileItem, EntityManagerInterface $entityManager): void
    {
        $children = $fileItem->getChildren();

        foreach ($children as $child) {
            if ($child->getChildren()->isEmpty()) {
                $path = $this->getParameter('files_manager_dir') . '/' . $child->getPath();

                if(is_file($path)) {
                    unlink($path);
                }

                $entityManager->remove($child);
            } else {
                $this->removeFileItemChildren($child, $entityManager);
            }
        }
    }
}
