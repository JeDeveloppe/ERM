<?php

namespace App\Controller\Site;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_notice', methods: ['GET'])]
    public function mentionsLegales(): Response
    {
        return $this->render('site/legal/mentions_legales.html.twig', [
            'title' => 'Mentions légales',
        ]);
    }
}
