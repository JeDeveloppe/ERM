<?php

namespace App\Controller\Site;

use App\Service\MapsService;
use App\Repository\ShopClassRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapsController extends AbstractController
{
    public function __construct(
        private MapsService $mapsService,
        private ShopClassRepository $shopClassRepository
    )
    {
    }

    // #[Route('/maps/cgovi-et-leur-centre', name: 'app_map_cgovi_and_shops')]
    // public function mapCgoViAndShops(): Response
    // {
    //     //?on recupere les donnees dans le service
    //     $donnees = $this->areasService->constructionOperationnalMapByShops();

    //     return $this->render('site/maps/operational_by_shops.html.twig', [
    //         'donnees' => $donnees,
    //     ]);
    // }

    #[Route('/maps/tous-les-centres-de-france', name: 'app_map_all_shops')]
    public function mapAllShops(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfAllShops();

        return $this->render('site/maps/all_shops.html.twig', [
            'donnees' => $donnees,
        ]);
    }

    #[Route('/maps/tous-les-centres-de-france-sous-cgo', name: 'app_map_all_shops_under_cgo')]
    public function mapAllShopsUnderCgo(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfAllShopsUnderCgo();

        return $this->render('site/maps/all_shops_under_cgo.html.twig', [
            'donnees' => $donnees,
        ]);
    }

    #[Route('/maps/toutes-les-regions', name: 'app_map_all_regions')]
    public function mapAllRegions(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfRegions();

        return $this->render('site/maps/all_regions.html.twig', [
            'donnees' => $donnees,
        ]);
    }

    #[Route('/maps/toutes-les-zones/{classeName}', name: 'app_map_all_zones')]
    public function mapAllZonesByClasse(string $classeName): Response
    {
        $classe = $this->shopClassRepository->findOneByName($classeName);

        if($classe){

            //?on recupere les donnees dans le service
            $donnees = $this->mapsService->constructionMapOfZonesByClasse($classe->getName());
    
            return $this->render('site/maps/all_zones.html.twig', [
                'donnees' => $donnees,
                'classeName' => $classe->getName(),
            ]);

        }else{
            
            throw $this->createNotFoundException('Zone non connue !');
            
        }
    }

    // #[Route('/maps/telematique', name: 'app_map_telematique')]
    // public function mapTelematiqueArea(): Response
    // {
    //     //?on recupere les donnees dans le service
    //     $donnees = $this->areasService->constructionMapOfTelematique();

    //     return $this->render('site/maps/telematic.html.twig', [
    //         'donnees' => $donnees,
    //     ]);
    // }
}
