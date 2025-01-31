<?php

namespace App\Controller;

use App\Form\SearchShopsByCityType;
use App\Service\CgoService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DistanceController extends AbstractController
{
    public function __construct(
        private CgoService $cgoService,
    ){}

    #[Route('/search-distance', name: 'app_search_distance')]
    public function searchDistance(Request $request): Response
    {

        $form = $this->createForm(SearchShopsByCityType::class);
        $form->handleRequest($request);

        if($request->isMethod('POST')){

            $form->submit($request->getPayload()->get($form->getName()));
    dd($form);
            if ($form->isSubmitted() && $form->isValid()) {

                $cityOfIntervention = $form->get('city')->getData();
                $datas = $this->cgoService->getDistances($cityOfIntervention);
            }
        }

        return $this->render('site/distance/distance.html.twig', [
            'formSearchByCity' => $form->createView(),
            'datas' => $datas ?? null,
            'title' => 'Recherche de distance'
        ]);
    }
}
