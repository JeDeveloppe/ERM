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

    #[Route('/maps/france/tous-les-centres', name: 'app_map_all_shops')]
    public function mapAllShops(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfAllShops();

        return $this->render('site/maps/all_shops.html.twig', [
            'donnees' => $donnees,
            'title' => 'Tous les centres ERM',
        ]);
    }

    #[Route('/maps/france/centres-sous-cgo-{classeName}', name: 'app_map_all_shops_under_cgo')]
    public function mapAllShopsUnderCgo(string $classeName): Response
    {
        $class = $this->shopClassRepository->findOneByName(strtoupper($classeName));

        if($class){

            //?on recupere les donnees dans le service
            $donnees = $this->mapsService->constructionMapOfAllShopsUnderCgo($class);
    
            return $this->render('site/maps/all_shops_under_cgo.html.twig', [
                'donnees' => $donnees,
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
        $donnees = $this->mapsService->constructionMapOfRegions();

        return $this->render('site/maps/all_regions.html.twig', [
            'donnees' => $donnees,
            'title' => 'Toutes les régions',
        ]);
    }

    #[Route('/maps/toutes-les-zones-{classeName}', name: 'app_map_all_zones')]
    public function mapAllZonesByClasse(string $classeName): Response
    {
        $classe = $this->shopClassRepository->findOneByName(strtoupper($classeName));

        if($classe){

            //?on recupere les donnees dans le service
            $donnees = $this->mapsService->constructionMapOfZonesByClasse($classe->getName());
    
            return $this->render('site/maps/all_zones.html.twig', [
                'donnees' => $donnees,
                'title' => 'Toutes les zones ' . $classe->getName(),
            ]);

        }else{
            
            throw $this->createNotFoundException('Zone non connue !');
            
        }
    }

    #[Route('/maps/telematique', name: 'app_map_telematique')]
    public function mapTelematiqueArea(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfTelematique();

        $telematicAreas = $this->telematicAreaRepository->findAll();

        return $this->render('site/maps/telematic.html.twig', [
            'donnees' => $donnees,
            'title' => 'Zones télématiques MV',
            'telematicAreas' => $telematicAreas
        ]);
    }
}
