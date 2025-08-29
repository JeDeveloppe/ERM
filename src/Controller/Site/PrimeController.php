<?php

namespace App\Controller\Site;

use App\Form\PrimeForTechniciansType;
use App\Repository\PrimelevelRepository;
use App\Service\PrimeLevelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrimeController extends AbstractController
{
    private int $primeMax;

    public function __construct(
        private PrimeLevelService $primeLevelService,
        private PrimelevelRepository $primelevelRepository,

    )
    {
        $this->primeMax = 600;
    }

    #[Route('/prime/calcul-prime-mensuelle', name: 'app_prime')]
    public function prime(Request $request): Response
    {
        //on récupere le formulaire par la request
        $form = $this->createForm(PrimeForTechniciansType::class);

        //on récupere les niveaux de prime en bdd
        $primeLevels = $this->primelevelRepository->findBy([], ['start' => 'ASC']);

        if($request->isMethod('POST')) {
            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);
                if($form->isSubmitted() && $form->isValid()) {
                    $divider = $form->get('divider')->getData();
                    $fullPs = $form->get('fullPs')->getData();

                    $psByPersonZone = $this->primeLevelService->getPsByPerson($fullPs, $divider);
                    $primeLevel = $this->primeLevelService->getPrimeLevel($psByPersonZone);

                    $primeByPerson = $this->primeLevelService->calculateValuePrimeByPerson($psByPersonZone, $primeLevel, $this->primeMax);

                    if($primeByPerson >= $this->primeMax){
                        $nextLevel = null;
                        $infosForNextLevel = [
                            'nextPsForNextLevel' => 'Maximum atteint !',
                            'psDifference' => 'Maximum atteint !',
                            'startPrime' => $this->primeMax,
                            'endPrime' => $this->primeMax
                        ];

                    }else{
                        $nextLevel = $this->primeLevelService->getPrimeLevel($primeLevel->getEnd());
                        $infosForNextLevel = $this->primeLevelService->returnInfosForNextLevel($divider, $fullPs, $nextLevel, $this->primeMax);
                    }
                }
        }

        return $this->render('site/prime/prime.html.twig', [
            'title' => 'Calcul prime mensuelle',
            'form' => $form,
            'primeLevels' => $primeLevels,
            'primeByPerson' => $primeByPerson ?? null,
            'divider' => $divider ?? null,
            'primeLevels' => $primeLevels ?? null,
            'primeLevelFromCalc' => $primeLevel ?? null,
            'nextLevel' => $nextLevel ?? null,
            'infosForNextLevel' => $infosForNextLevel ?? null,
        ]);
    }
}
