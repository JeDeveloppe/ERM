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

    #[Route('/prime', name: 'app_prime', methods: ['GET', 'POST'])]
    public function prime(Request $request): Response
    {
        //on récupere le formulaire par la request
        $form = $this->createForm(PrimeForTechniciansType::class);

        //on récupere les niveaux de prime en bdd
        $primeLevels = $this->primelevelRepository->findAll();

        if($request->isMethod('POST')) {
            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);
                if($form->isSubmitted() && $form->isValid()) {
                    $divider = $form->get('divider')->getData();
                    $fullPs = $form->get('fullPs')->getData();
                    $psByPerson = $this->primeLevelService->getPsByPerson($fullPs, $divider);
                    $primeLevel = $this->primeLevelService->getPrimeLevel($psByPerson);
                    if($primeLevel === null){
                        
                        $result = null;
                        $nextLevel = null;
                        $infosForNextLevel = null;
                        
                    }else{
                        
                        $result = $this->primeLevelService->getValuePrimeByPerson($psByPerson, $primeLevel);
                        $nextLevel = $this->primeLevelService->getPrimeLevel($primeLevel->getEnd() + 1);

                        if($nextLevel === null){
                            $infosForNextLevel = $this->primeLevelService->returnInfosForNextLevel($divider, $fullPs, $primeLevel);
                        }else{
                            $infosForNextLevel = $this->primeLevelService->returnInfosForNextLevel($divider, $fullPs, $nextLevel);
                        }
                    }
                }
        }

        return $this->render('site/prime/prime.html.twig', [
            'title' => 'Estimation prime mensuelle',
            'form' => $form->createView(),
            'primeLevels' => $primeLevels,
            'result' => $result ?? null,
            'nextLevel' => $nextLevel ?? null,
            'infosForNextLevel' => $infosForNextLevel ?? null,
        ]);
    }
}
