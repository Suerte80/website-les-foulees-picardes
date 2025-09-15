<?php

namespace App\Controller;

use App\Dto\ChangePasswordData;
use App\Entity\Member;
use App\Form\ChangePasswordType;
use App\Form\MemberType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member', name: 'member_')]
#[IsGranted("ROLE_USER")]
final class MemberController extends AbstractController
{
    /**
     * @return Response Rend le twig qui affiche toutes les informations du membre.
     *
     * Cette page sert à afficher toutes les informations de l'utilisateur.
     */
    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        /**
         * @var $member Member
         */
        $member = $this->getUser();

        $form = $this->createForm(MemberType::class, null, [
            'value' => [
                'email' => $member->getEmail(),
                'firstname' => $member->getFirstname(),
                'lastname' => $member->getLastname(),
                'address' => $member->getAddress(),
                'dateOFBirth' => $member->getDateOfBirth(),
                'phone' => $member->getPhone(),
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /* todo */
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

        if($request->isXmlHttpRequest()) {
            if($form->isSubmitted() && $form->isValid()) {
                $hashedPassword = $passwordHasher->hashPassword($member, $data->newPassword);

                $member->setPassword($hashedPassword);

                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été modifier.');

                return $this->redirectToRoute('member_index', []);
            }

            return $this->render('member/_change_password_form.html.twig', [
                'form' => $form->createView(),
            ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        return $this->render('member/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
