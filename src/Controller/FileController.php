<?php

namespace App\Controller;

use App\Entity\FileItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

#[Route('/files', name: 'app_files_')]
#[IsGranted('ROLE_BUREAU')]
final class FileController extends AbstractController
{
    #[Route('/upload', name: 'upload')]
    public function upload(Request $request, EntityManagerInterface $entityManager): Response
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

            $entityManager->persist($file);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_files_list');
    }

    #[Route('/', name: 'list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $files = $entityManager->getRepository(FileItem::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
        );

        return $this->render('file/list.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route('/download/{id}', name: 'download')]
    public function download(FileItem $fileItem): Response
    {
        $path = $this->getParameter('files_manager_dir') . '/' . $fileItem->getPath();

        return $this->file($path, $fileItem->getName());
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(FileItem $fileItem, EntityManagerInterface $entityManager): Response
    {
        $path = $this->getParameter('files_manager_dir') . '/' . $fileItem->getPath();
        if(is_file($path)) {
            unlink($path);
        }

        $entityManager->remove($fileItem);
        $entityManager->flush();

        return $this->redirectToRoute('app_files_list');
    }
}
