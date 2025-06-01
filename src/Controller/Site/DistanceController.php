<?php

namespace App\Controller\Site;

use App\Form\SearchShopsByCityType;
use App\Repository\ShopRepository;
use App\Service\CgoService;
use App\Service\MapsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DistanceController extends AbstractController
{
    public function __construct(
        private CgoService $cgoService,
        private MapsService $mapsService,
        private ShopRepository $shopRepository
    ){}

    #[Route('/distance/search-distance', name: 'app_search_distance', methods: ['GET', 'POST'])]
    public function searchDistance(Request $request): Response
    {

        $optionsAccepted = ['depannage','telematique']; //? options from SearchShopsByCityType
        
        $form = $this->createForm(SearchShopsByCityType::class);

        if($request->isMethod('POST')){

            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if($form->isSubmitted() && $form->isValid()){

                $cityOfIntervention = $form->get('city')->getData();
                $option = $form->get('options')->getData();
                    if(!in_array($option, $optionsAccepted)){
                        $option = 'depannage';
                    }

                $datas = $this->cgoService->getShopsByClassErmAndOptionArroundCityOfIntervention($cityOfIntervention, ['MX','MV'], $option);
                $map = $this->mapsService->getMapWithInterventionPointAndAllShopsArround($cityOfIntervention, $datas, $option);
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
