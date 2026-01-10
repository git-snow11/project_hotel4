<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomePageController extends AbstractController
{
    #[Route('/welcome/page', name: 'app_welcome_page')]
    public function index(): Response
    {
         if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin');
            }
            return $this->redirectToRoute('app_home');
        }
        return $this->render('welcome_page/index.html.twig', [
            'controller_name' => 'WelcomePageController',
        ]);
    }
}