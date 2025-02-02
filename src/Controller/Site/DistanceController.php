<?php

namespace App\Controller\Site;

use App\Form\SearchShopsByCityType;
use App\Service\CgoService;
use App\Service\MapsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

class DistanceController extends AbstractController
{
    public function __construct(
        private CgoService $cgoService,
        private MapsService $mapsService
    ){}

    #[Route('/search-distance', name: 'app_search_distance', methods: ['GET', 'POST'])]
    public function searchDistance(Request $request): Response
    {

        $form = $this->createForm(SearchShopsByCityType::class);

        if($request->isMethod('POST')){

            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if($form->isSubmitted() && $form->isValid()){

                $cityOfIntervention = $form->get('city')->getData();
                $datas = $this->cgoService->getDistances($cityOfIntervention);

                $map = $this->mapsService->getMapWithInterventionPointAndAllShopsArround($cityOfIntervention, $datas);
            }
        }

        return $this->render('site/distance/distance.html.twig', [
            'formSearchByCity' => $form->createView(),
            'datas' => $datas ?? null,
            'map' => $map ?? null,
            'title' => 'Recherche de distance'
        ]);
    }
}
