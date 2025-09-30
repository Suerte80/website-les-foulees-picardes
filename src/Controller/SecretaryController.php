<?php

namespace App\Controller;

use App\Entity\MembershipRequest;
use App\Repository\MembershipRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/board', name: 'app_secretary_')]
#[IsGranted('ROLE_SECRETAIRE')]
final class SecretaryController extends AbstractController
{
    #[Route('/membership/requests', name: 'requests')]
    public function membershipRequests(MembershipRequestRepository $requestRepository): Response
    {
        $membershipRequests = $requestRepository->findBy(['status' => 'pending']);

        return $this->render('board/membership_requests.html.twig', [
            'membership_requests' => $membershipRequests
        ]);
    }

    #[Route('/membership/requests/{id}/validate', name: 'validate', methods: ['POST'])]
    public function membershipRequestValidate(MembershipRequest $membershipRequest, EntityManagerInterface $entityManager): Response
    {
        $membershipRequest->setValidatedBy($this->getUser());
        $membershipRequest->setStatus('active');
        $membershipRequest->setUpdatedAt(new \DateTimeImmutable('now'));

        $member = $membershipRequest->getRequester();
        $member->setMembershipStatus('active');
        $member->setUpdatedAt(new \DateTime('now'));
        // TODO set membership_expires_at
        $member->setIsVerified(true);

        $entityManager->flush();

        return $this->json(
            ['ok' => true, 'id' => $membershipRequest->getId(), 'status' => 'validated']
        );
    }

    #[Route('/membership/requests/{id}', name: 'reject', methods: ['POST'])]
    public function membershipRequestReject(MembershipRequest $membershipRequest, EntityManagerInterface $entityManager): Response
    {
        $membershipRequest->setValidatedBy($this->getUser());
        $membershipRequest->setStatus('rejected');
        $membershipRequest->setUpdatedAt(new \DateTimeImmutable('now'));

        $member = $membershipRequest->getRequester();
        $member->setMembershipStatus('rejected');
        $member->setDeletedAt(new \DateTimeImmutable('now'));
        $member->setIsVerified(true);

        $entityManager->flush();

        return $this->json(
            ['ok' => true, 'id' => $membershipRequest->getId(), 'status' => 'rejected']
        );
    }
}
