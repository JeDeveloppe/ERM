<?php

namespace App\Controller\Site;

use App\Form\SearchCityForRoadAssistanceType;
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

    #[Route('/distance/search-distance-for-road-assistance', name: 'app_search_distance_for_road_assistance', methods: ['GET', 'POST'])]
    public function searchDistanceForRoadAssistance(Request $request): Response
    {

        $isSearchDone = false;
        
        $form = $this->createForm(SearchCityForRoadAssistanceType::class, null, []);

        if($request->isMethod('POST')){

            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if($form->isSubmitted() && $form->isValid()){

                $isSearchDone = true;
                $cityOfIntervention = $form->get('city')->getData();
                $option = $form->get('options')->getData();
                $classErm = ['MX','MV']; //?on cherche un dépannage par default

                $datas = $this->cgoService->getShopsByClassErmAndOptionArroundCityOfIntervention($cityOfIntervention, $classErm, $option);
                $map = $this->mapsService->getMapWithInterventionPointAndAllShopsArround($cityOfIntervention, $datas, $option);
            }
        }

        return $this->render('site/distance/road_assistance.html.twig', [
            'formSearchByCity' => $form->createView(),
            'datas' => $datas ?? null,
            'map' => $map ?? null,
            'isSearchDone' => $isSearchDone,
            'title' => 'Recherche de distance'
        ]);
    }


}
