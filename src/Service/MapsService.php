<?php

namespace App\Service;

use App\Entity\Cgo;
use App\Entity\City;
use App\Entity\Person;
use App\Entity\Department;
use Twig\Environment;
use Symfony\UX\Map\Map;
use App\Entity\ShopClass;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Polygon;
// use Symfony\UX\Map\Icon\Icon;
use App\Core\UxMap\CustomIcon as Icon;

use Symfony\UX\Map\InfoWindow;
use App\Repository\CgoRepository;
use App\Repository\ShopRepository;
use App\Repository\ShopClassRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;
use App\Repository\DepartmentRepository;
use App\Repository\PersonRepository;
use App\Repository\TelematicAreaRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;

class MapsService
{

    public function __construct(
            private RequestStack $requestStack,
            private ShopRepository $shopRepository,
            private ZoneErmRepository $zoneErmRepository,
            private RegionErmRepository $regionErmRepository,
            private TelematicAreaRepository $telematicAreaRepository,
            private PersonRepository $personRepository,
            private CgoRepository $cgoRepository,
            private KernelInterface $kernel,
            private DepartmentRepository $departmentRepository,
            private ShopClassRepository $shopClassRepository,
            private Environment $twig
        ){}

    private $COLORS_OF_MARKERS = "#0029D2";

    // Tolérance (en degrés) de la simplification Douglas-Peucker appliquée aux
    // polygones de département : ~0.01° ≈ 1km, largement suffisant pour une carte
    // de la France entière tout en gardant des formes reconnaissables.
    private const POLYGON_SIMPLIFICATION_TOLERANCE = 0.01;

    public function constructionMapOfZonesTelematique(): Map
    {
        $map = $this->generationUxMapWithBaseOptions();
        $areas = $this->telematicAreaRepository->findAllWithRelations();

        foreach($areas as $area)
        {
            //?si la zone contient au moins 1 département
            if($area->getDepartments()->count() === 0){
                continue;
            }

            $color = $area->getTerritoryColor() ?? $this->randomHexadecimalColor();

            $contactCgo = "";
            foreach($area->getCgos() as $cgo){
                //?comment contacter le cgo
                if($cgo->getManager() !== null){

                    $contactCgo .= '<p><b>' . $cgo->getName() . '</b><br/> ' . $cgo->getManager()->getFirstName() . ' ' . $cgo->getManager()->getName() . ' <br/> ' . $cgo->getManager()->getPhone() . '<br/>' . $cgo->getManager()->getEmail().'</p>';

                }else{

                    $contactCgo = "MANAGER DE CGO NON RENSEIGNÉ !";
                }
            }

            $this->addDepartmentPolygonsToMap($map, $area->getDepartments(), fn(Department $department) => [
                'label' => $department->getName().' ('.$department->getCode().')',
                'color' => $color,
                'content' => $contactCgo,
            ]);

            //?on boucle sur les plusieurs cgo possible de la zone
            foreach($area->getCgos() as $cgo){
                if (!$cgo->getCity()) {
                    continue;
                }

                $map->addMarker(new Marker(
                    position: new Point($cgo->getCity()->getLatitude(), $cgo->getCity()->getLongitude()),
                    icon: Icon::url('../../map/images/logoCgo.png')->width(32)->height(32),
                    title: $cgo->getName(),
                    infoWindow: new InfoWindow(
                        headerContent: $cgo->getName(),
                        content: $contactCgo,
                    ),
                ));
            }
        }

        return $map;
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
            if (!$cgo->getCity()) {
                continue;
            }
            $locations[] =
            [
                "lat" => $cgo->getCity()->getLatitude(),
                "lng" => $cgo->getCity()->getLongitude(),
                "color" => $cgo->getTerritoryColor() ?? $this->randomHexadecimalColor(),
                "name" => $cgo->getName().' ('.$cgo->getCm().')',
                "description" => $cgo->getManager() ? $cgo->getManager()->getFirstName().' '.$cgo->getManager()->getName() : 'MANAGER DE CGO NON RENSEIGNÉ !',
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

                if($shop->getRcsPerson() !== null){

                    $manager = $shop->getRcsPerson();
                    $contactShop = $manager->getFirstName() . ' ' . $manager->getName() . ' <br/> ' . $shop->getRcsPerson()->getPhone() . '<br/>' . $manager->getEmail();
                
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
        //?on recupere tous les centres (ville + personnes/rôles préchargés en 1 requête)
        $shops = $this->shopRepository->findAllWithCityAndPeopleRoles();

        $map = $this->generationUxMapWithBaseOptions();

        foreach($shops as $shop)
        {
            if (!$shop->getCity()) {
                continue;
            }

            $iconOfShopUnderCgo = Icon::ux('solar:garage-bold')->width(12)->height(12)->color($this->COLORS_OF_MARKERS);

            $map->addMarker(new Marker(
                position: new Point($shop->getCity()->getLatitude(), $shop->getCity()->getLongitude()),
                icon: $iconOfShopUnderCgo,
                title: $shop->getName(),
                infoWindow: new InfoWindow(
                    content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getRcsPerson()?->getNameAndFirstName().'<br/>'.$shop->getPhone().'</p>',
                ),
                extra: [
                    'markerColor' => $this->COLORS_OF_MARKERS
                ]
            ));
        }

        return $map;
    }

    public function constructionMapOfRegions(): Map
    {
        $map = $this->generationUxMapWithBaseOptions(hasMarkersToFit: false);
        $regionErms = $this->regionErmRepository->findAll();

        foreach($regionErms as $regionErm)
        {
            $color = $regionErm->getTerritoryColor() ?? $this->randomHexadecimalColor();

            //?les départements rattachés directement à la région
            $this->addDepartmentPolygonsToMap($map, $regionErm->getDepartments(), fn(Department $department) => [
                'label' => $regionErm->getName(),
                'color' => $color,
            ]);
        }

        return $map;
    }

    public function constructionMapOfZonesByClasseWithUx(string $classeName): Map
    {
        $map = $this->generationUxMapWithBaseOptions(hasMarkersToFit: false);

        // Les zones en base gardent encore l'ancien nom "MV" (jamais renommées en
        // "MX" lors du découpage VL/VI/MX des centres).
        $zoneClasseName = $classeName === 'MX' ? 'MV' : $classeName;

        //on recupere toutes les zones
        $zones = $this->zoneErmRepository->findByClasse($zoneClasseName);

        // Les zones sont des territoires à l'échelle de la ville : un même département
        // peut contenir des centres rattachés à plusieurs zones différentes. On ne
        // garde qu'une seule zone par département (la première trouvée, comme pour les
        // régions) pour éviter de redessiner le même polygone plusieurs fois.
        $departments = [];
        $stylesByDepartment = [];

        foreach($zones as $zone){

            $color = $zone->getTerritoryColor() ?? $this->randomHexadecimalColor();

            $managerOfZone = $zone->getManager();
            if($managerOfZone !== null){
                $roleNames = implode(', ', array_map(fn($role) => $role->getName(), $managerOfZone->getRoles()->toArray()));
                $zoneContact = $managerOfZone->getFirstName() . ' ' . $managerOfZone->getName() . ' ('.$roleNames.') <br/> ' . $managerOfZone->getPhone() . '<br/>' . $managerOfZone->getEmail();
            }else{
                $zoneContact = "MANAGER NON RENSEIGNÉ";
            }

            foreach($zone->getShops() as $shop){
                if($shop->getCity() === null || $shop->getCity()->getDepartment() === null){
                    continue;
                }

                $department = $shop->getCity()->getDepartment();
                if(isset($stylesByDepartment[$department->getId()])){
                    continue;
                }

                $departments[$department->getId()] = $department;
                $stylesByDepartment[$department->getId()] = [
                    'label' => $zone->getName(),
                    'color' => $color,
                    'content' => $zoneContact,
                ];
            }
        }

        $this->addDepartmentPolygonsToMap($map, $departments, fn(Department $department) => $stylesByDepartment[$department->getId()]);

        return $map;
    }

    public function randomHexadecimalColor()
    {
        $rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        $color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];

        return $color;
    }

    /**
     * Ajoute un polygone coloré par département sur la carte.
     *
     * @param iterable<Department> $departments
     * @param callable(Department): (array{label: string, color: string, content?: string}|null) $styleResolver
     */
    private function addDepartmentPolygonsToMap(Map $map, iterable $departments, callable $styleResolver): void
    {
        foreach($departments as $department){

            $gpsPoints = $department->getGpsPoints();
            if(!$gpsPoints){
                continue;
            }

            $style = $styleResolver($department);
            if(!$style){
                continue;
            }

            foreach($this->geoJsonToPolygonPathGroups($gpsPoints) as $paths){
                if(!$paths){
                    continue;
                }

                $map->addPolygon(new Polygon(
                    points: $paths,
                    infoWindow: new InfoWindow(
                        headerContent: $style['label'],
                        content: $style['content'] ?? ($department->getName().' ('.$department->getCode().')'),
                    ),
                    extra: [
                        'fillColor' => $style['color'],
                        'color' => '#333333',
                        'fillOpacity' => 0.45,
                        'weight' => 1,
                    ],
                ));
            }
        }
    }

    /**
     * Convertit une géométrie GeoJSON (Polygon ou MultiPolygon) en un tableau de
     * "paths" (chaque path = anneau extérieur + éventuels trous), un groupe par
     * polygone disjoint (utile pour les MultiPolygon).
     *
     * @return array<int, array<int, array<int, Point>>>
     */
    private function geoJsonToPolygonPathGroups(array $gpsPoints): array
    {
        $type = $gpsPoints['type'] ?? null;
        $coordinates = $gpsPoints['coordinates'] ?? [];

        if($type === 'MultiPolygon'){
            $groups = [];
            foreach($coordinates as $polygonRings){
                $groups[] = $this->ringsToPointPaths($polygonRings);
            }

            return $groups;
        }

        //?'Polygon' (ou format inconnu, traité comme un seul polygone)
        return [$this->ringsToPointPaths($coordinates)];
    }

    /**
     * @return array<int, array<int, Point>>
     */
    private function ringsToPointPaths(array $rings): array
    {
        $paths = [];
        foreach($rings as $ring){
            $coords = [];
            foreach($ring as $coord){
                if(isset($coord[0], $coord[1]) && is_numeric($coord[0]) && is_numeric($coord[1])){
                    $coords[] = [(float) $coord[0], (float) $coord[1]];
                }
            }

            // Les frontières GeoJSON stockées sont en résolution complète (jusqu'à
            // 35 000 points pour un seul département côtier) : bien trop détaillé pour
            // une carte de la France entière, ça sature la mémoire PHP. On simplifie.
            $coords = $this->simplifyRing($coords, self::POLYGON_SIMPLIFICATION_TOLERANCE);

            $path = array_map(fn(array $coord) => new Point($coord[1], $coord[0]), $coords);
            if($path){
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Simplification de Douglas-Peucker : réduit le nombre de points d'un anneau
     * tout en conservant sa forme générale (les points les plus "structurants" sont gardés).
     *
     * @param array<int, array{0: float, 1: float}> $points
     * @return array<int, array{0: float, 1: float}>
     */
    private function simplifyRing(array $points, float $tolerance): array
    {
        $count = count($points);
        if($count < 3){
            return $points;
        }

        $dMax = 0.0;
        $index = 0;
        $start = $points[0];
        $end = $points[$count - 1];

        for($i = 1; $i < $count - 1; $i++){
            $distance = $this->perpendicularDistance($points[$i], $start, $end);
            if($distance > $dMax){
                $index = $i;
                $dMax = $distance;
            }
        }

        if($dMax > $tolerance){
            $left = $this->simplifyRing(array_slice($points, 0, $index + 1), $tolerance);
            $right = $this->simplifyRing(array_slice($points, $index), $tolerance);

            return array_merge(array_slice($left, 0, -1), $right);
        }

        return [$start, $end];
    }

    /**
     * @param array{0: float, 1: float} $point
     * @param array{0: float, 1: float} $lineStart
     * @param array{0: float, 1: float} $lineEnd
     */
    private function perpendicularDistance(array $point, array $lineStart, array $lineEnd): float
    {
        [$x, $y] = $point;
        [$x1, $y1] = $lineStart;
        [$x2, $y2] = $lineEnd;

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        if($dx === 0.0 && $dy === 0.0){
            return sqrt(($x - $x1) ** 2 + ($y - $y1) ** 2);
        }

        $t = (($x - $x1) * $dx + ($y - $y1) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0.0, min(1.0, $t));

        $closestX = $x1 + $t * $dx;
        $closestY = $y1 + $t * $dy;

        return sqrt(($x - $closestX) ** 2 + ($y - $closestY) ** 2);
    }

    public function constructionMapOfAllShopsUnderCgo(ShopClass $classErm)
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();

        // Un CGO n'a lui-même jamais la classe MV/MX : c'est le CGO VI qui pilote
        // aussi les centres MV/MX (comme pour les zones télématiques).
        $cgoClassErm = in_array($classErm->getName(), ['MX', 'MV'], true)
            ? $this->shopClassRepository->findOneBy(['name' => 'VI'])
            : $classErm;

        //?on recupere tous les cgos
        $cgos = $this->cgoRepository->findBy(['classErm' => $cgoClassErm]);

        $locations = []; //? toutes les réponses seront dans ce tableau final

        foreach($cgos as $cgo)
        {
            //?le cgo
            if ($cgo->getCity()) {
                $locations[] =
                [
                    "lat" => $cgo->getCity()->getLatitude(),
                    "lng" => $cgo->getCity()->getLongitude(),
                    "color" => $cgo->getTerritoryColor() ?? $this->randomHexadecimalColor(),
                    "name" => $cgo->getName().' ('.$cgo->getCm().')',
                    "description" => $cgo->getManager() ? $cgo->getManager()->getFirstName().' '.$cgo->getManager()->getName() : 'MANAGER DE CGO NON RENSEIGNÉ !',
                    "size" => 30,
                    "type" => "image",
                    "image_url" => "https://erm.je-developpe.fr/map/images/logoCgo.png"
                ];
            }

            $shops = $cgo->getShopsUnderControls();

            foreach($shops as $shop)
            {
                //?si on a les coordonnees de renseignées dans la base uniquement
                if(!is_null($shop->getCity()))
                {
    
                    if($shop->getRcsPerson() !== null){
    
                        $manager = $shop->getRcsPerson();
                        $contactShop = $manager->getFirstName() . ' ' . $manager->getName() . ' <br/> ' . $shop->getRcsPerson()->getPhone() . '<br/>' . $manager->getEmail();
                    
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
        $infoWindowInterventionPointContent = $this->twig->render(
            'site/maps/popups/interventionPoint_infowindow.html.twig',
            ['city' => $cityOfIntervention]
        );
        
        $map
            ->addMarker( new Marker(
                position: new Point($cityOfIntervention->getLatitude(), $cityOfIntervention->getLongitude()),
                title: $cityOfIntervention->getName(),
                infoWindow: new InfoWindow(
                    headerContent: $cityOfIntervention->getName(),
                    content: $infoWindowInterventionPointContent
                ),
                icon: $iconOfIntervention,
                // extra: [
                //     'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                // ],
            ));

        foreach($arrayFromAllShopsNearCityOfIntervention as $data){
            $shop = $data['shop'];

            if($option == 'telematique'){ 
                $icon = Icon::ux('ri:taxi-wifi-fill')->width(24)->height(24)->color($this->COLORS_OF_MARKERS);
            } else {
                $icon = Icon::ux('solar:garage-bold')->width(24)->height(24)->color($this->COLORS_OF_MARKERS);
            }

            // Render the Twig template and pass the necessary data
            $infoWindowContent = $this->twig->render(
                'site/maps/popups/people_infowindow.html.twig',
                [
                    'shop' => $shop,
                    'data' => $data,
                    'cityOfIntervention' => $cityOfIntervention,
                    'option' => $option,
                ]
            );

            $map
                ->addMarker( new Marker(
                    position: new Point($data['shop']->getCity()->getLatitude(), $data['shop']->getCity()->getLongitude()),
                    title: $data['shop']->getName(),
                    icon: $icon,
                    infoWindow: new InfoWindow(
                        // headerContent: $shop->getName().' ('.$shop->getCm().')',
                        content: $infoWindowContent
                    ),
                    extra: [
                        'markerColor' => $this->COLORS_OF_MARKERS
                    ],
                ));
        }

        return $map;
    }

    public function constructionMapOfAllShopsUnderCgoWithUxMap(ShopClass $classErm)
    {

        // Un CGO n'a lui-même jamais la classe MV/MX : c'est le CGO VI qui pilote
        // aussi les centres MV/MX (comme pour les zones télématiques).
        $cgoClassErm = in_array($classErm->getName(), ['MX', 'MV'], true)
            ? $this->shopClassRepository->findOneBy(['name' => 'VI'])
            : $classErm;

        //?on recupere tous les cgos
        $cgos = $this->cgoRepository->findByClassErmWithRelations($cgoClassErm);

        $map = $this->generationUxMapWithBaseOptions();

        $iconOfCgo = Icon::url('../../map/images/logoCgo.png')->width(32)->height(32);


        foreach($cgos as $cgo)
        {
            if (!$cgo->getCity()) {
                continue;
            }

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
                if (!$shop->getCity()) {
                    continue;
                }

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
                        content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getRcsPerson()?->getNameAndFirstName().'<br/>'.$shop->getPhone().'</p>',
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

    public function constructionMapOfPeopleTelematique(array $formationNames, array $functionNames, array $vehicleNames)
    {

        //?on recupere tous les techniciens
        $people = $this->personRepository->findAllTelematicPeople($formationNames, $functionNames, $vehicleNames);

        //?on cré un manager et un Cgo fakes
        $fakeManager = new Person();
        $fakeManager->setFirstName('MANAGER NON RENSEIGNÉ')->setPhone("TÉLÉPHONE NON RENSEIGNÉ")->setEmail("EMAIL NON RENSEIGNÉ");
        $fakeCgo = new Cgo();
        $fakeCgo->setTerritoryColor('#000000')->setName("PAS DE CGO RENSEIGNÉ")->setManager($fakeManager);

        //?on construit la map
        $map = $this->generationUxMapWithBaseOptions();

        foreach($people as $person)
        {
            if (!$person->getShop() || !$person->getShop()->getCity()) {
                continue;
            }

            $cgo = $person->getControledByCgo();
            if(!$cgo){
                $cgo = $fakeCgo;
            }

            $color = $cgo->getTerritoryColor();

            $iconOfPerson = Icon::ux('ri:taxi-wifi-fill')->width(24)->height(24)->color($color);
            $infoWindowContent = $this->getInfoWindowContentForPersonTelematic($person, $cgo);

             $map->addMarker(new Marker(
                    position: new Point($person->getShop()->getCity()->getLatitude(), $person->getShop()->getCity()->getLongitude()),
                    icon: $iconOfPerson,
                    title: $person->getName(),
                    infoWindow: new InfoWindow(content: $infoWindowContent),
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
        $cts = $this->personRepository->findByRole(\App\Entity\RoleErm::CT);

        // Départements couverts par chaque CT (son centre de rattachement + les
        // centres qu'il inspecte), pour colorer la carte comme pour les régions.
        // Un même département n'est colorié qu'une fois (premier CT trouvé).
        $departments = [];
        $stylesByDepartment = [];

        //?on construit la map
        foreach($cts as $ct)
        {
            if (!$ct->getShop() || !$ct->getShop()->getCity()) {
                continue;
            }

            $color = $ct->getZoneColor() ?? $this->randomHexadecimalColor();
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
                position: new Point($ct->getShop()->getCity()->getLatitude(), $ct->getShop()->getCity()->getLongitude()),
                icon: $iconOfTechnicalAdvisor,
                title: $ct->getShop()->getName(),
                infoWindow: new InfoWindow(
                    headerContent: $ct->getFirstName().' '.$ct->getName(),
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
                if (!$shop->getCity()) {
                    continue;
                }

                $iconOfShopUnderCt = Icon::ux('gravity-ui:target')->width(34)->height(34)->color($color);

                $mapOnlyWithShops->addMarker(new Marker(
                    position: new Point($shop->getCity()->getLatitude(), $shop->getCity()->getLongitude()),
                    icon: $iconOfShopUnderCt,
                    title: $shop->getName(),
                    infoWindow: new InfoWindow(
                        content: $shop->getName().'('.$shop->getCm().')<p>'.$shop->getRcsPerson()?->getNameAndFirstName().'<br/>'.$shop->getPhone().'</p>',
                    ),
                    // Ajoutez la couleur dans le tableau 'extra'
                    extra: [
                        'markerColor' => $color, // Passez votre variable $color ici
                    ],
                ));
            }

            $ctShops = $ct->getWorkForShops()->toArray();
            $ctShops[] = $ct->getShop();

            foreach($ctShops as $ctShop){
                if(!$ctShop->getCity() || !$ctShop->getCity()->getDepartment()){
                    continue;
                }

                $department = $ctShop->getCity()->getDepartment();
                $ctName = $ct->getFirstName().' '.$ct->getName();

                if(isset($stylesByDepartment[$department->getId()])){
                    // Plusieurs CT peuvent couvrir le même département (pas de
                    // territoire exclusif) : on garde la couleur du premier CT
                    // trouvé, mais on liste tous les CT dans le libellé.
                    if(!str_contains($stylesByDepartment[$department->getId()]['label'], $ctName)){
                        $stylesByDepartment[$department->getId()]['label'] .= ', '.$ctName;
                    }
                    continue;
                }

                $departments[$department->getId()] = $department;
                $stylesByDepartment[$department->getId()] = [
                    'label' => $ctName,
                    'color' => $color,
                ];
            }

        }

        // Les plaques de couleur par département n'ont de sens que sur la vue
        // "carte des CT" (une couleur = un CT) ; sur la vue "tous les centres",
        // elles n'apportent rien et gênent la lecture des marqueurs de centres.
        if($optionName == 'cts'){
            $this->addDepartmentPolygonsToMap($mapOnlyWithCts, $departments, fn(Department $department) => $stylesByDepartment[$department->getId()]);
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

    /**
     * @param bool $hasMarkersToFit false pour les cartes sans marqueur (régions/zones
     *  en polygones) : on fixe alors un centre/zoom valides sur la France nous-mêmes,
     *  sinon la carte resterait au zoom(4) par défaut (sous le minZoom(6) du fond de
     *  carte) et aucune tuile ne s'afficherait. Pour les cartes AVEC marqueurs, on ne
     *  fixe rien ici : fitBoundsToMarkers() recalculera la bonne position une fois les
     *  marqueurs ajoutés — fixer un centre/zoom ici en plus ferait charger les tuiles
     *  OSM deux fois (une fois pour cette position initiale, une fois pour la position
     *  finale), ce qui multiplie inutilement les requêtes.
     */
    public function generationUxMapWithBaseOptions(bool $hasMarkersToFit = true)
    {
        $map = (new Map())->fitBoundsToMarkers($hasMarkersToFit);

        if(!$hasMarkersToFit){
            $map->center(new Point(46.6, 2.2))->zoom(6);
        }

        // Fond de carte clair (CartoDB Positron) plutôt que l'OSM standard : les
        // zones vertes de l'OSM standard écrasaient les couleurs des polygones
        // (départements/zones/régions).
        $leafletOptions = (new LeafletOptions())
            ->tileLayer(new TileLayer(
                url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
                options: [
                    'minZoom' => 6,
                    'maxZoom' => 10,
                    'subdomains' => 'abcd',
                ]
                ));
        // Add the custom options to the map
        $map->options($leafletOptions);

        // Cantonne la carte à la France métropolitaine + Corse : évite de pouvoir
        // dézoomer/glisser jusqu'à voir le reste du monde. Lu côté JS par
        // mymap_controller.js (ux:map:connect) via Leaflet's setMaxBounds().
        $map->extra([
            'maxBounds' => [[41.0, -5.5], [51.5, 10.0]],
        ]);

        return $map;
    }

    public function getInfoWindowContentForPersonTelematic($person): string
    {
        return $this->twig->render('site/maps/popups/person_telematic_infowindow.html.twig', [
            'person' => $person
        ]);
    }

}