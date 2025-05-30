<?php

namespace App\Controller\Site;

use App\Form\SearchTechnicianFormationsTypeForm;
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
        // $mapDonnees = $this->mapsService->constructionMapOfAllShops();
        $mapDonnees = $this->mapsService->constructionMapOfAllShopsWithUx();

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

        return $this->render('site/maps/zones_telematic.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Zones télématiques MV',
            'telematicAreas' => $telematicAreas
        ]);
    }

    #[Route('/maps/techniciens-telematique/{formationName?}', name: 'app_map_technicians_telematique', methods: ['GET', 'POST'])]
    public function mapTechniciansTelematiqueArea(?array $formationNames, Request $request): Response
    {

        // Create the form, associating it with the $film object
        $form = $this->createForm(SearchTechnicianFormationsTypeForm::class);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form was submitted and is valid
        if($form->isSubmitted() && $form->isValid()) {

            $formations = $form->get('name')->getData();
            $formationNamesArray = $formations->toArray();

            $formationNames = [];
            foreach($formationNamesArray as $formationName){
                $formationNames[] = $formationName->getName();
            }

            $mapDonnees = $this->mapsService->constructionMapOfTechniciansTelematique($formationNames);

        }else{
            
            $formationNames = [];
            //?on recupere les donnees dans le service
            $mapDonnees = $this->mapsService->constructionMapOfTechniciansTelematique($formationNames);
        }

        return $this->render('site/maps/technicians_telematic.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Les techniciens télématiques',
            'form' => $form->createView()
        ]);
    }
}
