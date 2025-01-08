<?php

namespace App\Service;

use App\Entity\ShopClass;
use App\Entity\TelematicArea;
use App\Repository\ShopRepository;
use App\Repository\CgoTelematicAreaRepository;
use App\Repository\CgoOperationalAreaRepository;
use App\Repository\CgoRepository;
use App\Repository\RegionErmRepository;
use App\Repository\TelematicAreaRepository;
use App\Repository\ZoneErmRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class MapsService
{

    public function __construct(
            private RequestStack $requestStack,
            private ShopRepository $shopRepository,
            private ZoneErmRepository $zoneErmRepository,
            private RegionErmRepository $regionErmRepository,
            private TelematicAreaRepository $telematicAreaRepository,
            private CgoRepository $cgoRepository
        ){}

    public function constructionMapOfTelematique()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        //?on recupere les shops concernés
        $areas = $this->telematicAreaRepository->findAll();

        $locations = []; //? toutes les réponses seront dans ce tableau final
        $states = []; //? toutes les réponses seront dans ce tableau final

        foreach($areas as $area)
        {
            //?on recupere le shop
            $cgo = $area->getCgo();

            //?comment contacter le shop
            if($cgo->getManager() !== null){

                $contactCgo = $cgo->getManager()->getFirstName() . ' ' . $cgo->getManager()->getLastName() . ' - ' . $cgo->getManager()->getPhone() . '<br/>' . $cgo->getManager()->getEmail();

            }else{

                $contactCgo = "MANAGER DE CGO NON RENSEIGNÉ !";
            }

            $locations[] = 
            [
                "lat" => $cgo->getCity()->getLatitude() ? $cgo->getCity()->getLatitude() : $cgo->getCity()->getLatitude(),
                "lng" => $cgo->getCity()->getLongitude() ? $cgo->getCity()->getLongitude() : $cgo->getCity()->getLongitude(),
                "color" => "#333",
                "name" => $cgo->getName(),
                "description" => $contactCgo,
                "url" => $baseUrl,
                "size" => 20,
            ];

            //?on traite les departements ratachées au shop
            $departments = $area->getDepartmentsUnderControl();
            foreach($departments as $department){
        
                $states[$department->getSimplemapCode()] =
                [
                    "name" => $department->getName().' ('.$department->getCode().')',
                    "description" => $contactCgo,
                    "color" => $area->getCgo()->getTerritoryColor(),
                ];
                
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $jsonStates = json_encode($states, JSON_FORCE_OBJECT); 

        $donnees['locations'] = $jsonLocations;
        $donnees['states'] = $jsonStates;

        return $donnees;
    }

    // public function constructionOperationnalMapByShops()
    // {

    //     //? on recupere l'url de base
    //     $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
    //     //?on recupere les shops concernés
    //     $areas = $this->shopOperationalAreaRepository->findAll();

    //     $locations = []; //? toutes les réponses seront dans ce tableau final

    //     foreach($areas as $area)
    //     {

    //         //?on recupere le shop
    //         $cgoWhoControlsTheArea = $area->getCgoWhoControlsTheArea();

    //         //?comment contacter le shop
    //         $managerFromCgoWhoControlsTheArea = $cgoWhoControlsTheArea->getManager();
    //         $contactCgoWhoControlsTheArea = $managerFromCgoWhoControlsTheArea->getFirstName() . ' ' . $managerFromCgoWhoControlsTheArea->getLastName() . ' <br/> ' . $managerFromCgoWhoControlsTheArea->getPhone() . '<br/>' . $managerFromCgoWhoControlsTheArea->getEmail();

    //         $locations[] = 
    //         [
    //             "lat" => $cgoWhoControlsTheArea->getLatitude() ? $cgoWhoControlsTheArea->getLatitude() : $cgoWhoControlsTheArea->getCity()->getLatitude(),
    //             "lng" => $cgoWhoControlsTheArea->getLongitude() ? $cgoWhoControlsTheArea->getLongitude() : $cgoWhoControlsTheArea->getCity()->getLongitude(),
    //             "color" => $cgoWhoControlsTheArea->getTerritoryColor(),
    //             "name" => $cgoWhoControlsTheArea->getName(),
    //             "description" => $contactCgoWhoControlsTheArea,
    //             "url" => $baseUrl,
    //             "size" => 20,
    //         ];

    //         //?on traite les departements ratachées au shop
    //         $shops = $area->getShopsUnderControl();
    //         foreach($shops as $shop){
                
    //             $contactShop = $shop->getManager()->getFirstName() . ' ' . $shop->getManager()->getLastName() . ' <br/> ' . $shop->getManager()->getPhone() . '<br/>' . $shop->getManager()->getEmail();
                
    //             $locations[] =
    //             [
    //                 "name" => $shop->getName(),
    //                 "description" => $contactShop,
    //                 "color" => $cgoWhoControlsTheArea->getTerritoryColor(),
    //                 "size" => 15,
    //                 "lat" => $shop->getLatitude(),
    //                 "lng" => $shop->getLongitude()
    //             ];
                
    //         }
    //     }

    //     //?on encode en json
    //     $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
    //     // $jsonStates = json_encode($locations, JSON_FORCE_OBJECT); 

    //     $donnees['locations'] = $jsonLocations;
    //     // $donnees['states'] = $jsonStates;

    //     return $donnees;
    // }

    public function constructionMapOfAllShops()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
        //?on recupere tous les centres
        $shops = $this->shopRepository->findAll();

        $locations = []; //? toutes les réponses seront dans ce tableau final

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
                    "size" => 15,
                ];
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $donnees['locations'] = $jsonLocations;

        return $donnees;
    }

    public function constructionMapOfRegions()
    {

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
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

        //? on recupere l'url de base
        $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();

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
                        "size" => 15,
                    ];
                }
            }
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $donnees['locations'] = $jsonLocations;

        return $donnees;
    }
}