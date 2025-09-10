<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MembershipRequest;
use App\Form\MembershipRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\RegistrationData;

#[Route('/membership')]
final class MembershipController extends AbstractController
{
    #[Route('/', name: 'app_membership')]
    public function index(): Response
    {
        return $this->render('membership/index.html.twig', [
            'controller_name' => 'MembershipController',
        ]);
    }

    #[Route('/request', name: 'app_membership_request')]
    public function request(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MembershipRequestType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \App\Dto\RegistrationData $registrationData
             */
            $registrationData = $form->getData();

            // On vérifie que l'addresse email existe déjà dans la base membre
            $existing = $entityManager->getRepository(Member::class)->findOneBy(['email' => $registrationData->email]);
            if($existing){
                $this->addFlash('error', 'Cet email existe déjà.');
                return $this->redirectToRoute('app_membership_request');
            }

            $member = new Member();
            $membershipRequest = new MembershipRequest();

            $member->setEmail($registrationData->email);
            $hashedPassword = $passwordHasher->hashPassword($member, $registrationData->password);
            $member->setPassword($hashedPassword);
            $member->setFirstName($registrationData->firstname);
            $member->setLastName($registrationData->lastname);
            $member->setAddress($registrationData->address);
            $member->setDateOfBirth($registrationData->dateOfBirth);
            $member->setPhone($registrationData->phone);

            $membershipRequest->setEmail($registrationData->email);
            $membershipRequest->setFirstName($registrationData->firstname);
            $membershipRequest->setLastName($registrationData->lastname);
            $membershipRequest->setMessage($registrationData->message);
            $membershipRequest->setRgpdAccepted($registrationData->rgpdAccepted);

            $entityManager->persist($member);
            $entityManager->persist($membershipRequest);

            $entityManager->flush();

            // TODO Envoyé un mail pour vérifié le mail !!!!

            return $this->redirectToRoute('app_membership_request_sent');
        }

        return $this->render('membership/request.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/requestSent', name: 'app_membership_request_sent')]
    public function requestSent(): Response
    {
        return $this->render('membership/requestSent.html.twig');
    }
}
