<?php

namespace App\Controller\Site;

use App\Form\PrimeForTechniciansType;
use App\Repository\PrimelevelRepository;
use App\Repository\StaffRepository;
use App\Service\PrimeLevelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrimeController extends AbstractController
{
    public function __construct(
        private PrimeLevelService $primeLevelService,
        private PrimelevelRepository $primelevelRepository,
    )
    {
    }

    #[Route('/prime', name: 'app_prime')]
    public function index(Request $request): Response
    {
        //on récupere le formulaire par la request
        $form = $this->createForm(PrimeForTechniciansType::class);
        $form->handleRequest($request);

        //on récupere les niveaux de prime en bdd
        $primeLevels = $this->primelevelRepository->findAll();

        if($form->isSubmitted() && $form->isValid()) {
    
            $divider = $form->get('divider')->getData();
            $fullPs = $form->get('fullPs')->getData();
            $result = $this->primeLevelService->calculPrimeByPersonByStaff($divider, $fullPs);

        }

        return $this->render('site/prime/prime.html.twig', [
            'title' => 'Estimation prime mensuelle',
            'form' => $form->createView(),
            'primeLevels' => $primeLevels,
            'result' => $result ?? null,
        ]);
    }
}
