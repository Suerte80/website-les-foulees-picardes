<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MembershipController extends AbstractController
{
    #[Route('/membership', name: 'app_membership')]
    public function index(): Response
    {
        return $this->render('membership/index.html.twig', [
            'controller_name' => 'MembershipController',
        ]);
    }
}
