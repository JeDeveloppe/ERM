<?php

namespace App\Controller\Site;

use App\Form\SearchCityForTelematicInterventionType;
use App\Service\CgoService;
use App\Service\MapsService;
use App\Repository\ShopRepository;
use App\Repository\ShopClassRepository;
use App\Repository\TelematicAreaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchTechnicianByDetailsTypeForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TelematicController extends AbstractController
{
    public function __construct(
        private MapsService $mapsService,
        private ShopClassRepository $shopClassRepository,
        private TelematicAreaRepository $telematicAreaRepository,
        private CgoService $cgoService,
        private ShopRepository $shopRepository
    ){}

    #[Route('/telematique/map/', name: 'app_map_zones_telematique')]
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

    #[Route('/telematique/maps/techniciens/{formationName?}', name: 'app_map_technicians_telematique', methods: ['GET', 'POST'])]
    public function mapTechniciansTelematiqueArea(?array $formationNames, Request $request): Response
    {

        // Create the form, associating it with the $film object
        $form = $this->createForm(SearchTechnicianByDetailsTypeForm::class);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form was submitted and is valid
        if($form->isSubmitted() && $form->isValid()) {

            $formations = $form->get('formations')->getData();
            $formationNamesArray = $formations->toArray();

            $functions = $form->get('fonctions')->getData();
            $functionNamesArray = $functions->toArray();

            $vehicles = $form->get('vehicles')->getData();
            $vehicleNamesArray = $vehicles->toArray();

            $formationNames = [];
            foreach($formationNamesArray as $formationName){
                $formationNames[] = $formationName->getName();
            }

            $functionNames = [];
            foreach($functionNamesArray as $functionName){
                $functionNames[] = $functionName->getName();
            }

            $vehicleNames = [];
            foreach($vehicleNamesArray as $vehicleName){
                $vehicleNames[] = $vehicleName->getName();
            }

            $mapDonnees = $this->mapsService->constructionMapOfTechniciansTelematique($formationNames, $functionNames, $vehicleNames);

        }else{
            
            $formationNames = [];
            $functionNames = [];
            $vehicleNames = [];
            //?on recupere les donnees dans le service
            $mapDonnees = $this->mapsService->constructionMapOfTechniciansTelematique($formationNames, $functionNames, $vehicleNames);
        }

        return $this->render('site/maps/technicians_telematic.html.twig', [
            'mapDonnees' => $mapDonnees,
            'title' => 'Les techniciens télématiques',
            'form' => $form->createView()
        ]);
    }

    #[Route('/telematique/search-distance-for-telematic-assistance', name: 'app_search_distance_for_telematic_assistance', methods: ['GET', 'POST'])]
    public function searchDistanceForTelematiqueAssistance(Request $request): Response
    {

        $isSearchDone = false;
        
        $form = $this->createForm(SearchCityForTelematicInterventionType::class, null, []);

        if($request->isMethod('POST')){

            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if($form->isSubmitted() && $form->isValid()){

                $isSearchDone = true;
                $cityOfIntervention = $form->get('city')->getData();
                $option = 'telematique';
                $classErm = ['MX','MV','VL']; //?on cherche toutes les classes

                $formations = $form->get('formations')->getData();
                
                $optionsTelematique = [
                    'formations' => $formations,
                    // Ajoutez les autres options si nécessaire, par exemple :
                    // 'fonctions' => array_map(fn($f) => $f->getId(), $fonctions),
                    // 'vehicles' => array_map(fn($v) => $v->getId(), $vehicles),
                ];


                $datas = $this->cgoService->getShopsByClassErmAndOptionArroundCityOfIntervention($cityOfIntervention, $classErm, $option, $optionsTelematique);
                $map = $this->mapsService->getMapWithInterventionPointAndAllShopsArround($cityOfIntervention, $datas, $option);
            }
        }

        return $this->render('site/telematic/distance.html.twig', [
            'formSearchByCity' => $form->createView(),
            'datas' => $datas ?? null,
            'map' => $map ?? null,
            'isSearchDone' => $isSearchDone,
            'title' => 'Recherche des techniciens les plus proches'
        ]);
    }
}
