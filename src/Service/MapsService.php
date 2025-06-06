<?php

namespace App\Service;

use App\Entity\Cgo;
use App\Entity\City;
use App\Entity\Manager;
use Symfony\UX\Map\Map;
use App\Entity\ShopClass;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Icon\Icon;
use Symfony\UX\Map\InfoWindow;
use App\Repository\CgoRepository;
use App\Repository\ShopRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;
use App\Repository\DepartmentRepository;
use App\Repository\TechnicalAdvisorRepository;
use App\Repository\TechnicianRepository;
use App\Repository\TelematicAreaRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\MapOptionsInterface;

class MapsService
{

    public function __construct(
            private RequestStack $requestStack,
            private ShopRepository $shopRepository,
            private ZoneErmRepository $zoneErmRepository,
            private RegionErmRepository $regionErmRepository,
            private TelematicAreaRepository $telematicAreaRepository,
            private TechnicianRepository $technicianRepository,
            private CgoRepository $cgoRepository,
            private KernelInterface $kernel,
            private DepartmentRepository $departmentRepository,
            private TechnicalAdvisorRepository $technicalAdvisorRepository
        ){}

    private $COLORS_OF_MARKERS = "#0029D2";

    public function constructionMapOfZonesTelematique()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        //?on recupere les shops concernés
        $areas = $this->telematicAreaRepository->findAll();

        $locations = []; //? toutes les réponses seront dans ce tableau final
        $states = []; //? toutes les réponses seront dans ce tableau final

        foreach($areas as $area)
        {
            //?si la zone contient au moins 1 département
            if($area->getDepartments()->count() > 0){

                $contactCgo = "";
                foreach($area->getCgos() as $cgo){
                    //?comment contacter le cgo
                    if($cgo->getManager() !== null){

                        $contactCgo .= '<p><b>' . $cgo->getName() . '</b><br/> ' . $cgo->getManager()->getFirstName() . ' ' . $cgo->getManager()->getLastName() . ' <br/> ' . $cgo->getManager()->getPhone() . '<br/>' . $cgo->getManager()->getEmail().'</p>';

                    }else{

                        $contactCgo = "MANAGER DE CGO NON RENSEIGNÉ !";
                    }
                }

                //?on boucle sur les plusieurs cgo possible de la zone
                foreach($area->getCgos() as $cgo){

                    $locations[] = 
                    [
                        "lat" => $cgo->getCity()->getLatitude() ? $cgo->getCity()->getLatitude() : $cgo->getCity()->getLatitude(),
                        "lng" => $cgo->getCity()->getLongitude() ? $cgo->getCity()->getLongitude() : $cgo->getCity()->getLongitude(),
                        "color" => "#000000",
                        "name" => $cgo->getName(),
                        "description" => $contactCgo,
                        "url" => $baseUrl,
                        "size" => 30,
                        "type" => "image",
                        "image_url" => "https://erm.je-developpe.fr/map/images/logoCgo.png"
                    ];


                    //?on traite les departements ratachées au shop
                    $departments = $area->getDepartments();
                    foreach($departments as $department){

                        $states[$department->getSimplemapCode()] =
                        [
                            "name" => $department->getName().' ('.$department->getCode().')',
                            "description" => $contactCgo,
                            "color" => $area->getTerritoryColor(),
                        ];
                        
                    }
                }
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $jsonStates = json_encode($states, JSON_FORCE_OBJECT); 

        $donnees['locations'] = $jsonLocations;
        $donnees['states'] = $jsonStates;

        return $donnees;
    }

    public function constructionMapOfAllShops()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        //?on recupere tous les centres
        $shops = $this->shopRepository->findAll();
        $cgos = $this->cgoRepository->findAll();

        $locations = []; //? toutes les réponses seront dans ce tableau final

        //?on boucle sur les cgos
        foreach($cgos as $cgo){
            $locations[] = 
            [
                "lat" => $cgo->getCity()->getLatitude(),
                "lng" => $cgo->getCity()->getLongitude(),
                "color" => $cgo->getTerritoryColor() ?? $this->randomHexadecimalColor(),
                "name" => $cgo->getName().' ('.$cgo->getCm().')',
                "description" => $cgo->getManager()->getFirstName().' '.$cgo->getManager()->getLastName(),
                "size" => 30,
                "type" => "image",
                "image_url" => "https://erm.je-developpe.fr/map/images/logoCgo.png"
            ];
        }


        foreach($shops as $shop)
        {
            //?si on a les coordonnees de renseignées dans la base uniquement
            if(!is_null($shop->getCity()))
            {

                if($shop->getManager() !== null){

                    $manager = $shop->getManager();
                    $contactShop = $manager->getFirstName() . ' ' . $manager->getLastName() . ' <br/> ' . $shop->getManager()->getPhone() . '<br/>' . $manager->getEmail();
                
                }else{

                    $contactShop = "NON RENSEIGNÉ";
                }
                
                $locations[] = 
                [
                    "lat" => $shop->getCity()->getLatitude(),
                    "lng" => $shop->getCity()->getLongitude(),
                    "color" => "#333",
                    "name" => $shop->getName().' ('.$shop->getCm().')',
                    "description" => $contactShop,
                    "url" => $baseUrl,
                    "size" => 10,
                ];
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $donnees['locations'] = $jsonLocations;

        return $donnees;
    }

    public function constructionMapOfAllShopsWithUx()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        //?on recupere tous les centres
        $shops = $this->shopRepository->findAll();

        $map = $this->generationUxMapWithBaseOptions();

        foreach($shops as $shop)
        {

            $iconOfShopUnderCgo = Icon::ux('solar:garage-bold')->width(12)->height(12)->color($this->COLORS_OF_MARKERS);

            $map->addMarker(new Marker(
                position: new Point($shop->getCity()->getLatitude(), $shop->getCity()->getLongitude()),
                icon: $iconOfShopUnderCgo,
                title: $shop->getName(),
                infoWindow: new InfoWindow(
                    content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getManager()->getFirstNameAndNameOnly().'<br/>'.$shop->getPhone().'</p>',
                ),
                extra: [
                    'markerColor' => $this->COLORS_OF_MARKERS
                ]
            ));
        }

        return $map;
    }

    public function constructionMapOfRegions()
    {

        //?on recupere tous les centres
        $regionErms = $this->regionErmRepository->findAll();
        //?on fait un tableau des couleurs des régions
        $regionColors = [];


        $states = []; //? toutes les réponses seront dans ce tableau final

        foreach($regionErms as $regionErm)
        {

            $regionColors[$regionErm->getName()] = $this->randomHexadecimalColor();

            //pour chaque zone ou récupère les centres
            foreach($regionErm->getZoneErms() as $zone){

                $shops = $zone->getShops();
                //pour chaque shop on recupere le département
                foreach($shops as $shop){
                    if($shop->getCity()){

                        $department = $shop->getCity()->getDepartment();
                        $states[$department->getSimplemapCode()] =
                            [
                                "name" => $regionErm->getName(),
                                "description" => $department->getName().' ('.$department->getCode().')',
                                "color" => $regionErm->getTerritoryColor() ?? $regionColors[$regionErm->getName()],
                            ];

                    }else{

                        $noCities[] = $shop->getName();
                    }
                }
            }
        }
        // $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $jsonStates = json_encode($states, JSON_FORCE_OBJECT); 

        // $donnees['locations'] = $jsonLocations;
        $donnees['states'] = $jsonStates;

        return $donnees;
    }

    public function constructionMapOfZonesByClasse(string $classeName)
    {

        $states = []; //? toutes les réponses seront dans ce tableau final

        //on recupere toutes les zones
        $zones = $this->zoneErmRepository->findByClasse($classeName);

        //pour chaque zone ou récupère les centres
        foreach($zones as $zone){

            $zoneName = $zone->getName();
            $zoneColors[$zoneName] = $this->randomHexadecimalColor();
            
            $shops = $zone->getShops();

            $managerOfZone = $zone->getManager();
            if($managerOfZone !== null){
                $zoneContact = $managerOfZone->getFirstName() . ' ' . $managerOfZone->getLastName() . ' ('.$managerOfZone->getManagerClass()->getName().') <br/> ' . $managerOfZone->getPhone() . '<br/>' . $managerOfZone->getEmail();
            }else{
                $zoneContact = "MANAGER NON RENSEIGNÉ";
            }

            //pour chaque shop on recupere le département
            foreach($shops as $shop){
                
                if($shop->getCity() !== null){

                    $department = $shop->getCity()->getDepartment();
                    
                    $states[$department->getSimplemapCode()] =
                        [
                            "name" => $department->getName().' ('.$department->getCode().')',
                            "description" => $zoneName.' <br/>'.$zoneContact,
                            "color" => $zone->getTerritoryColor() ?? $zoneColors[$zoneName],
                        ];
                }
            }
        }

        // $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $jsonStates = json_encode($states, JSON_FORCE_OBJECT); 

        // $donnees['locations'] = $jsonLocations;
        $donnees['states'] = $jsonStates;

        return $donnees;
    }

    public function randomHexadecimalColor()
    {
        $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        $color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];

        return $color;
    }

    public function constructionMapOfAllShopsUnderCgo(ShopClass $classErm)
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        
        //?on recupere tous les cgos
        $cgos = $this->cgoRepository->findBy(['classErm' => $classErm]);

        $locations = []; //? toutes les réponses seront dans ce tableau final

        foreach($cgos as $cgo)
        {
            //?le cgo
            $locations[] = 
            [
                "lat" => $cgo->getCity()->getLatitude(),
                "lng" => $cgo->getCity()->getLongitude(),
                "color" => $cgo->getTerritoryColor() ?? $this->randomHexadecimalColor(),
                "name" => $cgo->getName().' ('.$cgo->getCm().')',
                "description" => $cgo->getManager()->getFirstName().' '.$cgo->getManager()->getLastName(),
                "size" => 30,
                "type" => "image",
                "image_url" => "https://erm.je-developpe.fr/map/images/logoCgo.png"
            ];
            
            $shops = $cgo->getShopsUnderControls();

            foreach($shops as $shop)
            {
                //?si on a les coordonnees de renseignées dans la base uniquement
                if(!is_null($shop->getCity()))
                {
    
                    if($shop->getManager() !== null){
    
                        $manager = $shop->getManager();
                        $contactShop = $manager->getFirstName() . ' ' . $manager->getLastName() . ' <br/> ' . $shop->getManager()->getPhone() . '<br/>' . $manager->getEmail();
                    
                    }else{
    
                        $contactShop = "NON RENSEIGNÉ";
                    }
                    
                    $locations[] = 
                    [
                        "lat" => $shop->getCity()->getLatitude(),
                        "lng" => $shop->getCity()->getLongitude(),
                        "color" => $cgo->getTerritoryColor() ?? $this->randomHexadecimalColor(),
                        "name" => $shop->getName().' ('.$shop->getCm().')',
                        "description" => $contactShop,
                        "url" => $baseUrl,
                        "size" => 10,
                    ];
                }
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $donnees['locations'] = $jsonLocations;

        return $donnees;
    }

    public function getMapWithInterventionPointAndAllShopsArround(City $cityOfIntervention, array $arrayFromAllShopsNearCityOfIntervention, string $option): Map
    {

        $map = $this->generationUxMapWithBaseOptions();

        $iconOfIntervention = Icon::ux('tabler:truck-filled')->width(42)->height(42);
        
        $map
            ->addMarker( new Marker(
                position: new Point($cityOfIntervention->getLatitude(), $cityOfIntervention->getLongitude()),
                title: $cityOfIntervention->getName(),
                infoWindow: new InfoWindow(
                    headerContent: $cityOfIntervention->getName(),
                    content: 'Lieu de l\'intervention'
                ),
                icon: $iconOfIntervention,
                // extra: [
                //     'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                // ],
            ));

        foreach($arrayFromAllShopsNearCityOfIntervention as $data){

            //?on recupere les cgos pour chaque shop
            $cgos = "Cgo(s) rattaché(s): <br>";
            if(count($data['shop']->getCgos()) > 0){
                foreach ($data['shop']->getCgos() as $cgo) {
                    $cgos .= $cgo->getName().'<br>';
                }
            }else{
                $cgos = "Aucun cgo rattaché";
            }

            if($option == 'telematique'){ //? options from SearchShopsByCityType
                $technicians = "<p>Technicien(s) télématique: <br>";
                $icon = Icon::ux('ri:taxi-wifi-fill')->width(24)->height(24)->color($this->COLORS_OF_MARKERS);
                foreach ($data['shop']->getTechnicians() as $technician) {
                    $technicians .= '- '.$technician->getName().': '.$technician->getPhone().'<br>';
                }
                $technicians .= '</p>';
            }else{
                $technicians = "";
                $icon = Icon::ux('solar:garage-bold')->width(24)->height(24)->color($this->COLORS_OF_MARKERS);
            }

            $map
                ->addMarker( new Marker(
                    position: new Point($data['shop']->getCity()->getLatitude(), $data['shop']->getCity()->getLongitude()),
                    title: $data['shop']->getName(),
                    icon: $icon,
                    infoWindow: new InfoWindow(
                        headerContent: $data['shop']->getName().' ('.$data['shop']->getCm().') <br/>'.$data['shop']->getPhone(),
                        content: $technicians.'<p>Distance : '.($data['distance'] / 1000).' kms <br>Temps de trajet : '.gmdate("H:i:s", $data['duration']).'</p>'.$cgos
                    ),
                    extra: [
                        // 'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                        'markerColor' => $this->COLORS_OF_MARKERS
                    ],
                ));
        }

        return $map;
    }

    public function constructionMapOfAllShopsUnderCgoWithUxMap(ShopClass $classErm)
    {

        //?on recupere tous les cgos
        $cgos = $this->cgoRepository->findBy(['classErm' => $classErm]);

        $map = $this->generationUxMapWithBaseOptions();

        $iconOfCgo = Icon::url('../../map/images/logoCgo.png')->width(32)->height(32);


        foreach($cgos as $cgo)
        {
            $map->addMarker(new Marker(
                position: new Point($cgo->getCity()->getLatitude(), $cgo->getCity()->getLongitude()),
                icon: $iconOfCgo,
                title: $cgo->getName(),
                infoWindow: new InfoWindow(
                    content: $cgo->getName(),
                )
            ));

            //tous les shops du cgo
            $shops = $cgo->getShopsUnderControls();

            foreach($shops as $shop)
            {
                $color = $cgo->getTerritoryColor();
                if (!is_string($color) || empty($color)) {
                    $color = '#000000'; // Default to black if color is missing/invalid
                    dump('Warning: Cgo ' . $cgo->getName() . ' has invalid territory color. Defaulting to black.');
                }

                $iconOfShopUnderCgo = Icon::ux('solar:garage-bold')->width(14)->height(14)->color($color);

                $map->addMarker(new Marker(
                    position: new Point($shop->getCity()->getLatitude(), $shop->getCity()->getLongitude()),
                    icon: $iconOfShopUnderCgo,
                    title: $shop->getName(),
                    infoWindow: new InfoWindow(
                        content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getManager()->getFirstNameAndNameOnly().'<br/>'.$shop->getPhone().'</p>',
                    ),
                    // Ajoutez la couleur dans le tableau 'extra'
                    extra: [
                        'markerColor' => $color, // Passez votre variable $color ici
                    ],
                ));
            }
            
        }

        return $map;
    }

    public function constructionMapOfTechniciansTelematique(array $formationNames)
    {

        //?on recupere tous les techniciens
        $technicians = $this->technicianRepository->findAllTelematicTechniciansByFormationName($formationNames);

        //?on cré un manager et un Cgo fakes
        $fakeManager = new Manager();
        $fakeManager->setFirstName('MANAGER NON RENSEIGNÉ')->setPhone("TÉLÉPHONE NON RENSEIGNÉ")->setEmail("EMAIL NON RENSEIGNÉ");
        $fakeCgo = new Cgo();
        $fakeCgo->setTerritoryColor('#000000')->setName("PAS DE CGO RENSEIGNÉ")->setManager($fakeManager);

        //?on construit la map
        $map = $this->generationUxMapWithBaseOptions();

        foreach($technicians as $technician)
        {
            $cgo = $technician->getControledByCgo();
            if(!$cgo){
                $cgo = $fakeCgo;
            }

            $color = $cgo->getTerritoryColor();

            $iconOfTechnician = Icon::ux('ri:taxi-wifi-fill')->width(24)->height(24)->color($color);
            $formations = '';
            foreach($technician->getTechnicianFormations() as $formation) {
                $formations .= '<span class="badge" style="background-color:'.$formation->getColor().'">'.$formation->getName().'</span> ';
            }

             $map->addMarker(new Marker(
                    position: new Point($technician->getShop()->getCity()->getLatitude(), $technician->getShop()->getCity()->getLongitude()),
                    icon: $iconOfTechnician,
                    title: $technician->getName(),
                    infoWindow: new InfoWindow(
                        content:
                         '<p>'.strtoupper($technician->getName()).' '.$technician->getFirstName().
                         '<br/>Tél: '.$technician->getPhone().'<br/>Email: '.$technician->getEmail().'</p>'.
                        '<p>Formations: '.$formations.
                         '</p>
                         <p>Géré par:<br/>'.$cgo->getName().'<br/>'.$cgo->getManager().'<br/>'.$cgo->getManager()->getPhone().'</p>'
                    ),
                    // Ajoutez la couleur dans le tableau 'extra'
                    extra: [
                        'markerColor' => $color, // Passez votre variable $color ici
                    ],
             ));
        }

        return $map;
    }

    public function constructionMapOfAllCtWihUxMap(string $optionName)
    {
        $mapOnlyWithShops = $this->generationUxMapWithBaseOptions();    
        $mapOnlyWithCts = $this->generationUxMapWithBaseOptions();
        
        //?on recupere tous les ct
        $cts = $this->technicalAdvisorRepository->findAll();

        //?on construit la map
        foreach($cts as $ct)
        {

            $color = $ct->getZoneColor();
            $iconOfTechnicalAdvisor = Icon::ux('fa6-solid:magnifying-glass-dollar')->width(24)->height(24)->color($color);
            $workForShops = '<p>Fait les inspections pour:<br/>';
            foreach($ct->getWorkForShops() as $shop) {
                if($shop){
                    $workForShops .= '<span class="badge" style="background-color:'.$ct->getZoneColor().'">'.$shop->getName().'</span> ';
                }else{
                    'AUCUN POUR LE MOMENT...';
                }
            }
            $workForShops .= '</p>';

            $mapOnlyWithCts->addMarker(new Marker(
                position: new Point($ct->getAttachmentCenter()->getCity()->getLatitude(), $ct->getAttachmentCenter()->getCity()->getLongitude()),
                icon: $iconOfTechnicalAdvisor,
                title: $ct->getAttachmentCenter()->getName(),
                infoWindow: new InfoWindow(
                    headerContent: $ct->getFirstName().' '.$ct->getLastName(),
                    content: '<p>Tél: '.$ct->getPhone().'<br/>Email: '.$ct->getEmail().'</p>'.$workForShops,
                ),
                extra: [
                    'markerColor' => $color,
                ]
            ));

            //tous les shops du cgo
            $shops = $ct->getWorkForShops();

            foreach($shops as $shop)
            {

                $iconOfShopUnderCt = Icon::ux('gravity-ui:target')->width(34)->height(34)->color($color);

                $mapOnlyWithShops->addMarker(new Marker(
                    position: new Point($shop->getCity()->getLatitude(), $shop->getCity()->getLongitude()),
                    icon: $iconOfShopUnderCt,
                    title: $shop->getName(),
                    infoWindow: new InfoWindow(
                        content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getManager()->getFirstNameAndNameOnly().'<br/>'.$shop->getPhone().'</p>',
                    ),
                    // Ajoutez la couleur dans le tableau 'extra'
                    extra: [
                        'markerColor' => $color, // Passez votre variable $color ici
                    ],
                ));
            }
            
        }

        //! choices from the form
        if($optionName == 'cts')
        {
            $map =  $mapOnlyWithCts;
        }
        else if($optionName == 'shops')
        {
            $map =  $mapOnlyWithShops;
        }

        return $map;
    }

    public function generationUxMapWithBaseOptions()
    {
        $map = (new Map())->center(new Point(48.8566, 2.3522))->zoom(4)->fitBoundsToMarkers(true);

        $leafletOptions = (new LeafletOptions())
            ->tileLayer(new TileLayer(
                url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                options: [
                    'minZoom' => 6,
                    'maxZoom' => 10,        
                ]
                ));
        // Add the custom options to the map
        $map->options($leafletOptions);

        return $map;
    }

}