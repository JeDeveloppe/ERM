<?php

namespace App\Controller\Site;

use App\Form\SearchShopsByCityType;
use App\Service\CgoService;
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

                $map = (new Map());
                $map
                    ->center(new Point($cityOfIntervention->getLatitude(), $cityOfIntervention->getLongitude()))
                    ->zoom(9);

                $map
                    ->addMarker( new Marker(
                        position: new Point($cityOfIntervention->getLatitude(), $cityOfIntervention->getLongitude()),
                        title: $cityOfIntervention->getName(),
                        infoWindow: new InfoWindow(
                            headerContent: $cityOfIntervention->getName(),
                            content: 'Lieu de l\'intervention'
                        ),
                        extra: [
                            'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                        ],
                    ));

                foreach($datas as $data){
                    $map
                        ->addMarker( new Marker(
                            position: new Point($data['shop']->getCity()->getLatitude(), $data['shop']->getCity()->getLongitude()),
                            title: $data['shop']->getName(),
                            infoWindow: new InfoWindow(
                                headerContent: $data['shop']->getName(),
                                content: 'Distance : '.$data['distance'].' mètres<br>Temps de trajet : '.$data['duration'].' secondes'
                            ),
                            extra: [
                                'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                            ],
                        ));
                }
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
