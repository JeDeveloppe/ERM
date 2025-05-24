<?php

namespace App\Controller\Site;

use App\Service\MapsService;
use App\Repository\ShopClassRepository;
use App\Repository\TelematicAreaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_shops'),
            'name' => 'Tous les centres'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_shops_under_cgo', ['classeName' => 'mv']),
            'name' => 'Tous les centres sous cgo MV'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_shops_under_cgo', ['classeName' => 'vl']),
            'name' => 'Tous les centres sous cgo VL'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_regions'),
            'name' => 'Toutes les régions'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_zones', ['classeName' => 'mv']),
            'name' => 'Toutes les zones MV'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_all_zones', ['classeName' => 'vl']),
            'name' => 'Toutes les zones VL'
        ];
        $routes[] = [
            'url' => $this->generateUrl('app_map_zones_telematique'),
            'name' => 'Toutes les zones télématiques'
        ];

        return $this->render('site/maps/choices.html.twig', [
            'title' => 'Les cartes',
            'routes' => $routes
        ]);
    }

    #[Route('/maps/france/tous-les-centres', name: 'app_map_all_shops')]
    public function mapAllShops(): Response
    {
        //?on recupere les donnees dans le service
        $mapDonnees = $this->mapsService->constructionMapOfAllShops();

        return $this->render('site/maps/all_shops.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Tous les centres ERM',
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
    
            return $this->render('site/maps/all_zones.html.twig', [
                'mapDonnees' => $mapDonnees,
                'title' => 'Toutes les zones ' . $classe->getName(),
            ]);

        }else{
            
            throw $this->createNotFoundException('Zone non connue !');
            
        }
    }

    #[Route('/maps/zones-telematique', name: 'app_map_zones_telematique')]
    public function mapZonesTelematiqueArea(): Response
    {
        //?on recupere les donnees dans le service
        $mapDonnees = $this->mapsService->constructionMapOfZonesTelematique();

        $telematicAreas = $this->telematicAreaRepository->findAll();

        return $this->render('site/maps/telematic.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Zones télématiques MV',
            'telematicAreas' => $telematicAreas
        ]);
    }

    #[Route('/maps/techniciens-telematique', name: 'app_map_technicians_telematique')]
    public function mapTechniciansTelematiqueArea(): Response
    {
        //?on recupere les donnees dans le service
        $mapDonnees = $this->mapsService->constructionMapOfTelematique();

        $telematicAreas = $this->telematicAreaRepository->findAll();

        return $this->render('site/maps/telematic.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Zones télématiques MV',
            'telematicAreas' => $telematicAreas
        ]);
    }
}
