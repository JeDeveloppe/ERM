<?php

namespace App\Service;


use App\Repository\ShopRepository;
use App\Repository\CgoTelematicAreaRepository;
use App\Repository\CgoOperationalAreaRepository;
use App\Repository\RegionErmRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class MapsService
{

    public function __construct(
            private RequestStack $requestStack,
            private ShopRepository $shopRepository,
            private RegionErmRepository $regionErmRepository
        ){}

    // public function constructionMapOfTelematique()
    // {

    //     //? on recupere l'url de base
    //     $baseUrl = $this->requestStack->getCurrentRequest()->getScheme() . '://' . $this->requestStack->getCurrentRequest()->getHttpHost() . $this->requestStack->getCurrentRequest()->getBasePath();
    //     //?on recupere les shops concernés
    //     $areas = $this->shopTelematicAreaRepository->findAll();

    //     $locations = []; //? toutes les réponses seront dans ce tableau final
    //     $states = []; //? toutes les réponses seront dans ce tableau final

    //     foreach($areas as $area)
    //     {
    //         //?on recupere le shop
    //         $shop = $area->getCgoWhoControlsArea();

    //         //?comment contacter le shop
    //         if($shop->getManager() !== null){

    //             $contactshop = $shop->getManager()->getFirstName() . ' ' . $shop->getManager()->getLastName() . ' - ' . $shop->getManager()->getPhone() . '<br/>' . $shop->getManager()->getEmail();

    //         }else{

    //             $contactshop = "INCONNU";
    //         }

    //         $locations[] = 
    //         [
    //             "lat" => $shop->getLatitude() ? $shop->getLatitude() : $shop->getCity()->getLatitude(),
    //             "lng" => $shop->getLongitude() ? $shop->getLongitude() : $shop->getCity()->getLongitude(),
    //             "color" => "#333",
    //             "name" => $shop->getName(),
    //             "description" => $contactshop,
    //             "url" => $baseUrl,
    //             "size" => 20,
    //         ];

    //         //?on traite les departements ratachées au shop
    //         $departments = $area->getDepartmentsUnderControl();
    //         foreach($departments as $department){
        
    //             $states[$department->getSimplemapCode()] =
    //             [
    //                 "name" => $department->getName().' ('.$department->getCode().')',
    //                 "description" => $contactshop,
    //                 "color" => $area->getZoneColor(),
    //             ];
                
    //         }
    //     }

    //     //?on encode en json
    //     $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
    //     $jsonStates = json_encode($states, JSON_FORCE_OBJECT); 

    //     $donnees['locations'] = $jsonLocations;
    //     $donnees['states'] = $jsonStates;

    //     return $donnees;
    // }

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
                    "name" => $shop->getName(),
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

        $states = []; //? toutes les réponses seront dans ce tableau final

        foreach($regionErms as $regionErm)
        {
            
            $states[$department->getSimplemapCode()] =
                [
                    "name" => $department->getName().' ('.$department->getCode().')',
                    "description" => $contactshop,
                    "color" => $area->getZoneColor(),
                ];
        }

        //?on encode en json
        $jsonLocations = json_encode($locations, JSON_FORCE_OBJECT); 
        $donnees['locations'] = $jsonLocations;

        return $donnees;
    }
}