<?php

namespace App\Controller\Site;

use App\Form\SearchTechnicianByDetailsTypeForm;
use App\Form\TechnicianAdvisorMapOptionsTypeForm;
use App\Service\MapsService;
use App\Repository\ShopClassRepository;
use App\Repository\TelematicAreaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MapsController extends AbstractController
{
    public function __construct(
        private MapsService $mapsService,
        private ShopClassRepository $shopClassRepository,
        private TelematicAreaRepository $telematicAreaRepository
    )
    {
    }

    #[Route('/maps', name: 'app_maps_choices')]
    public function mapsChoices(): Response
    {
        $routes = [];

          $carte = [
            'isGranted' => 'ROLE_ERM',
            'title' => 'Cartes:',
            'routes' => [
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_shops'),
                'name' => 'Tous les centres',
                'icon' => 'solar:garage-bold'
                ],
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_shops_under_cgo', ['classeName' => 'mv']),
                'name' => 'Tous les centres sous cgo MV',
                'icon' => 'solar:garage-bold'
                ],
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_shops_under_cgo', ['classeName' => 'vl']),
                'name' => 'Tous les centres sous cgo VL',
                'icon' => 'solar:garage-bold'
                ]
            ]
         ];
        $routes[] = $carte;

        $zone = [
            'isGranted' => 'ROLE_ERM',
            'title' => 'Zones:',
            'routes' => [
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_regions'),
                'name' => 'Toutes les régions'
                ],
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_zones', ['classeName' => 'mv']),
                'name' => 'Toutes les zones MV'
                ],
                [
                'isGranted' => 'ROLE_ERM',
                'url' => $this->generateUrl('app_map_all_zones', ['classeName' => 'vl']),
                'name' => 'Toutes les zones VL'
                ]
            ]
        ];
        $routes[] = $zone;

        $telematic = [
            'isGranted' => 'ROLE_MCF',
            'title' => 'Télématique:',
            'routes' => [
                [
                    'isGranted' => 'ROLE_MCF',
                    'url' => $this->generateUrl('app_map_zones_telematique'),
                    'name' => 'Toutes les zones télématiques'
                ],
                [
                    'isGranted' => 'ROLE_MCF',
                    'url' => $this->generateUrl('app_search_distance_for_road_assistance'),
                    'name' => 'Calculer une distance pour une intervention',
                    'icon' => 'game-icons:path-distance'
                ],
                [
                    'isGranted' => 'ROLE_MCF',
                    'url' => $this->generateUrl('app_map_technicians_telematique'),
                    'name' => 'Carte des téchniciens télématiques',
                    'icon' => 'ri:taxi-wifi-fill'
                ],
            ]
        ];
        $routes[] = $telematic;

        $ct = [
            'isGranted' => 'ROLE_ERM',
            'title' => 'CT:',
            'routes' => [
                [
                    'isGranted' => 'ROLE_ERM',
                    'url' => $this->generateUrl('app_map_all_cts'),
                    'name' => 'Carte des CT',
                    'icon' => 'fa6-solid:magnifying-glass-dollar'
                ]
            ]
        ];
        $routes[] = $ct;

        return $this->render('site/maps/home.html.twig', [
            'title' => 'Accueil',
            'routes' => $routes
        ]);
    }

    #[Route('/maps/france/tous-les-centres', name: 'app_map_all_shops')]
    public function mapAllShops(): Response
    {
        //?on recupere les donnees dans le service
        // $mapDonnees = $this->mapsService->constructionMapOfAllShops();
        $mapDonnees = $this->mapsService->constructionMapOfAllShopsWithUx();

        return $this->render('site/maps/all_shops.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Tous les centres ERM',
        ]);
    }

    #[Route('/maps/france/ct/{optionNameChoice?}', name: 'app_map_all_cts')]
    public function mapAllCts(Request $request, ?string $optionNameChoice): Response
    {

        $form = $this->createForm(TechnicianAdvisorMapOptionsTypeForm::class);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form was submitted and is valid
        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $optionName = $data['options'];
            $optionsNamePossibilities = ['cts', 'shops']; //! choices from the form

            if(!in_array($optionName, $optionsNamePossibilities)){
                $optionName = 'cts';
            }

            //?on recupere les donnees dans le service
            $mapDonnees = $this->mapsService->constructionMapOfAllCtWihUxMap($optionName);

        }else{
            //?on recupere les donnees dans le service
            $mapDonnees = $this->mapsService->constructionMapOfAllCtWihUxMap('cts');
        }

        return $this->render('site/maps/all_cts.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Carte des CT',
            'form' => $form
        ]);
    }

    #[Route('/maps/france/centres-sous-cgo-{classeName}', name: 'app_map_all_shops_under_cgo')]
    public function mapAllShopsUnderCgo(string $classeName): Response
    {
        $class = $this->shopClassRepository->findOneByName(strtoupper($classeName));

        if($class){

            //?on recupere les donnees dans le service
            // $mapDonnees = $this->mapsService->constructionMapOfAllShopsUnderCgo($class);
            $mapDonnees = $this->mapsService->constructionMapOfAllShopsUnderCgoWithUxMap($class);
    
            return $this->render('site/maps/all_shops_under_cgo.html.twig', [
                'mapDonnees' => $mapDonnees,
                'title' => 'Tous les centres sous CGO ' . $class->getName(),
            ]);

        }else{

            throw $this->createNotFoundException('Cette classe n\'existe pas');
        }

    }

    #[Route('/maps/toutes-les-regions', name: 'app_map_all_regions')]
    public function mapAllRegions(): Response
    {
        //?on recupere les donnees dans le service
        $mapDonnees = $this->mapsService->constructionMapOfRegions();

        return $this->render('site/maps/all_regions.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Toutes les régions',
        ]);
    }

    #[Route('/maps/toutes-les-zones-{classeName}', name: 'app_map_all_zones')]
    public function mapAllZonesByClasse(string $classeName): Response
    {
        $classe = $this->shopClassRepository->findOneByName(strtoupper($classeName));

        if($classe){

            //?on recupere les donnees dans le service
            $mapDonnees = $this->mapsService->constructionMapOfZonesByClasse($classe->getName());
            //$mapDonnees = $this->mapsService->constructionMapOfZonesByClasseWithUx($classe->getName());
    
            return $this->render('site/maps/all_zones.html.twig', [
                'mapDonnees' => $mapDonnees,
                'title' => 'Toutes les zones ' . $classe->getName(),
            ]);

        }else{
            
            throw $this->createNotFoundException('Zone non connue !');
            
        }
    }

}
