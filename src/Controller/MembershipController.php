<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MembershipRequest;
use App\Form\MembershipRequestType;
use App\Repository\MemberRepository;
use App\Repository\MembershipRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Dto\RegistrationData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/membership')]
final class MembershipController extends AbstractController
{
    #[Route('/', name: 'app_membership')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_membership_request');
    }

    #[Route('/request', name: 'app_membership_request')]
    public function request(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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

            $tokenHash = $this->generateSecureTokenAndHash();

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

            $membershipRequest->setMessage($registrationData->message);
            $membershipRequest->setRgpdAccepted($registrationData->rgpdAccepted);
            $membershipRequest->setRequester($member);
            $membershipRequest->setVerificationTokenHash($tokenHash['hash']);

            // TODO Envoyé un mail pour vérifié le mail !!!!
            $email = (new TemplatedEmail())
                ->from('do-not-respond@lesfouleespicardes.fr')
                ->to($member->getEmail())
                ->subject('Confirmation de votre email')
                ->htmlTemplate('email/email_membership_request.html.twig')
                ->context([
                    'user' => $member,
                    'link' => $this->generateUrl('app_membership_verify', ['token' => $tokenHash['token']], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            try{
                $mailer->send($email);
            } catch (\Throwable $e){
                dump($e);
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_membership_request');
            }

            // On flush les entities après l'envoi du mail au cas s'il y a une erreur.
            $entityManager->persist($member);
            $entityManager->persist($membershipRequest);

            $entityManager->flush();

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

    #[Route('/verify/{token}', name: 'app_membership_verify', requirements: ['token' => '[A-Za-z0-9_-]{20,200}'], methods: ['GET'])]
    public function verifyEmail(string $token, MembershipRequestRepository $membershipReqRep, EntityManagerInterface $entityManager): Response
    {
        // On hash le token
        $hash = hash('sha256', $token);

        // On recherche le hash dans les membershipRequest
        $membership = $membershipReqRep->findOneBy(['verificationTokenHash' => $hash]);

        // On vérifie
        if( $membership ){
            // On vérifie que le mail n'a pas été déjà vérifié.
            if($membership->isEmailVerified()){
                return $this->render('membership/emailIsAlreadyVerified.html.twig');
            }

            // Dans ce cas, on a bien trouvé une correspondance.
            $membership->setIsEmailVerified(true);
            $membership->setEmailVerifiedAt(new \DateTimeImmutable('now'));

            $membership->getRequester()->setIsVerified(true);

            $entityManager->flush();

            $this->addFlash('success', 'Votre email a été validé.');
            return $this->redirectToRoute('app_index');
        } else{
            // Dans ce cas aucun membershipRequest n'a été trouvé avec ce hash de token
            return $this->render('membership/error_verify_email.html.twig', []);
        }
    }

    /**
     * @return array Contenant la clé token qui contient un token généré aléatoirement de 64 chars et une clé content le token hasher avec la méthode sha256 (64 chars)
     */
    public function generateSecureTokenAndHash(): array
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        return [
            'token' => $token,
            'hash' => $hash
        ];
    }

    public function verififyToken(string $token, string $hash): bool
    {
        // on hash le token (méthode sha256)
        $tempHash = hash('sha256', $token);

        // On compare les hashs via la méthode hash_equals
        return hash_equals($hash, $tempHash);
    }
}
