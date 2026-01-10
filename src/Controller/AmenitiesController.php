<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AmenitiesController extends AbstractController
{
    #[Route('/amenities', name: 'app_amenities')]
    public function index(): Response
    {
        return $this->render('amenities/index.html.twig', [
            'controller_name' => 'AmenitiesController',
        ]);
    }
}
