<?php

namespace App\Controller;

use App\Entity\SocialRun;
use App\Form\SocialRunType;
use App\Repository\SocialRunRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/social/run')]
final class SocialRunController extends AbstractController
{
    #[Route(name: 'app_social_run_index', methods: ['GET'])]
    #[IsGranted('git ')]
    public function index(SocialRunRepository $socialRunRepository): Response
    {
        return $this->render('social_run/index.html.twig', [
            'social_runs' => $socialRunRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_social_run_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VIE_ASSO')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $socialRun = new SocialRun();
        $form = $this->createForm(SocialRunType::class, $socialRun);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($socialRun);
            $entityManager->flush();

            return $this->redirectToRoute('app_social_run_index', [], Response::HTTP_SEE_OTHER);
        }

        $response = $form->isSubmitted() && !$form->isValid()
            ? new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY)
            : null;

        return $this->render('social_run/new.html.twig', [
            'social_run' => $socialRun,
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/{id}', name: 'app_social_run_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(SocialRun $socialRun): Response
    {
        return $this->render('social_run/show.html.twig', [
            'social_run' => $socialRun,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_social_run_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VIE_ASSO')]
    public function edit(Request $request, SocialRun $socialRun, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SocialRunType::class, $socialRun);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_social_run_index', [], Response::HTTP_SEE_OTHER);
        }

        $response = $form->isSubmitted() && !$form->isValid()
            ? new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY)
            : null;

        return $this->render('social_run/edit.html.twig', [
            'social_run' => $socialRun,
            'form' => $form->createView(),
        ], $response);
    }

    #[Route('/{id}', name: 'app_social_run_delete', methods: ['POST'])]
    #[IsGranted('ROLE_VIE_ASSO')]
    public function delete(Request $request, SocialRun $socialRun, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$socialRun->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($socialRun);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_social_run_index', [], Response::HTTP_SEE_OTHER);
    }
}
