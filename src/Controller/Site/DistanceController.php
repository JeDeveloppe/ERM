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
        $isSearchDone = false;

        $isMCF = $this->isGranted('ROLE_MCF') && !$this->isGranted('ROLE_ERM');

        if($isMCF){
            $formOptions = [
                'choices' => ['Afficher les téchniciens télématiques les plus proches' => 'telematique'],
                'placeholder' => false
            ];
        }else{
            $formOptions = [
                'choices' => [
                    'Afficher les centres MV et MX les plus proches' => 'depannage',
                    'Afficher les téchniciens télématiques les plus proches' => 'telematique'
                ],
                'placeholder' => 'Choisir une option...'
            ];
        }
        
        $form = $this->createForm(SearchShopsByCityType::class, null, [
            'formOptions' => $formOptions
        ]);

        if($request->isMethod('POST')){

            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if($form->isSubmitted() && $form->isValid()){

                $isSearchDone = true;
                $cityOfIntervention = $form->get('city')->getData();
                $option = $form->get('options')->getData();
                $classErm = ['MX','MV']; //?on cherche un dépannage par default

                    if(!in_array($option, $optionsAccepted)){
                        $option = 'depannage';
                    }else{
                        if($option == 'telematique'){
                            $classErm = ['MX','MV','VL'];//?on cherche un telematique
                        }
                    }

                $datas = $this->cgoService->getShopsByClassErmAndOptionArroundCityOfIntervention($cityOfIntervention, $classErm, $option);
                $map = $this->mapsService->getMapWithInterventionPointAndAllShopsArround($cityOfIntervention, $datas, $option);
            }
        }

        return $this->render('site/distance/distance.html.twig', [
            'formSearchByCity' => $form->createView(),
            'datas' => $datas ?? null,
            'map' => $map ?? null,
            'isSearchDone' => $isSearchDone,
            'title' => 'Recherche de distance'
        ]);
    }

}
