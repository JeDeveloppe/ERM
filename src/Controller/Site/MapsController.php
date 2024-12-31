<?php

namespace App\Controller\Site;

use App\Service\MapsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MapsController extends AbstractController
{
    public function __construct(
        private MapsService $mapsService
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

    #[Route('/maps/tous-les-centres', name: 'app_map_all_shops')]
    public function mapAllShops(): Response
    {
        //?on recupere les donnees dans le service
        $donnees = $this->mapsService->constructionMapOfAllShops();

        return $this->render('site/maps/all_shops.html.twig', [
            'donnees' => $donnees,
        ]);
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
