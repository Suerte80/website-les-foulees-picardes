<?php

namespace App\Controller;

use App\Dto\ChangePasswordData;
use App\Entity\Member;
use App\Form\ChangePasswordType;
use App\Form\MemberType;
use App\Service\AvatarManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member', name: 'app_member_')]
#[IsGranted("ROLE_USER")]
final class MemberController extends AbstractController
{
    /**
     * @return Response Rend le twig qui affiche toutes les informations du membre.
     *
     * Cette page sert à afficher toutes les informations de l'utilisateur.
     */
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $entityManager, AvatarManager $avatarManager): Response
    {
        /**
         * @var $member Member
         */
        $member = $this->getUser();

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $uploaded = $form->get('avatar')->getData();

            if($uploaded){
                $newName = $avatarManager->handle($uploaded, $member->getAvatarFilename());
                $member->setAvatarFilename($newName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour.');
            return $this->redirectToRoute('app_member_index');
        }

        return $this->render('member/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Permet de changer le mot de passe du membre.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/change-password', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var $member Member */
        $member = $this->getUser();

        $data = new ChangePasswordData();
        $form = $this->createForm(ChangePasswordType::class, $data);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($member, $data->newPassword);
            $member->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            if($request->isXmlHttpRequest()) {
                $freshForm = $this->createForm(ChangePasswordType::class, new ChangePasswordData());

                return $this->render('member/_change_password_form.html.twig', [
                    'form' => $freshForm->createView(),
                ]);
            }

            return $this->redirectToRoute('app_member_index');
        }

        if($request->isXmlHttpRequest() && $form->isSubmitted()) {
            return $this->render('member/_change_password_form.html.twig', [
                'form' => $form->createView(),
            ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        return $this->render('member/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/avatar/delete', name: 'avatar_delete', methods: ['POST'])]
    public function deleteAvatar(
        EntityManagerInterface $entityManager,
        AvatarManager $avatarManager,
    ): Response {
        /** @var Member $member */
        $member = $this->getUser();
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if($member->getAvatarFilename()) {
            $avatarManager->delete($member->getAvatarFilename());
            $member->setAvatarFilename(null);
            $entityManager->flush();
            $this->addFlash('success', 'Avatar supprimé.');
        } else{
            $this->addFlash('info', 'Aucun avatar à supprimer.');
        }

        return $this->redirectToRoute('app_member_index');
    }
}
