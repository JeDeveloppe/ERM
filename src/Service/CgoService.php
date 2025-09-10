<?php

namespace App\Service;

use App\Entity\Cgo;
use App\Entity\City;
use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\Manager;
use App\Entity\ZoneErm;
use App\Entity\RegionErm;
use App\Repository\CgoRepository;
use App\Repository\CityRepository;
use App\Repository\ManagerClassRepository;
use App\Repository\ShopRepository;
use App\Repository\ManagerRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;

use App\Repository\ShopClassRepository;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CgoService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopClassRepository $shopClassRepository,
        private ZoneErmRepository $zoneErmRepository,
        private ManagerRepository $managerRepository,
        private CityRepository $cityRepository,
        private CgoRepository $cgoRepository,
        private RegionErmRepository $regionErmRepository,
        private ManagerClassRepository $managerClassRepository,
        private MapsService $mapsService,
        private ShopRepository $shopRepository,
        private HttpClientInterface $client,
        private TechnicianRepository $technicianRepository
        ){
    }

    public function importCgos(SymfonyStyle $io): void
    {
        $io->title('Importation des CGO ERM');

            $totals = $this->readCsvFile();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdate($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush();
            }
            

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function readCsvFile(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/cgos.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): Cgo
    {
        
        $manager = $this->managerRepository->findOneByLastName($arrayEntity['Nom responsable']);
        if(!$manager){
            $manager = new Manager();
            $manager->setFirstName('Prénom responsable')
                ->setLastName($arrayEntity['Nom responsable'])
                ->setEmail('Email responsable')
                ->setPhone('Mobile responsable')
                ->setManagerClass($this->managerClassRepository->findOneByName('RCGO'));
            $this->em->persist($manager);
            $this->em->flush($manager);
        }

        $regionErm = $this->regionErmRepository->findOneByName($arrayEntity['Région']);
        if(!$regionErm){
            $regionErm = new RegionErm();
            $regionErm->setName($arrayEntity['Région']);
            $this->em->persist($regionErm);
            $this->em->flush($regionErm);
        }

        $cgo = $this->cgoRepository->findOneByCm($arrayEntity['CM']);

        if(!$cgo){
            $cgo = new Cgo();
        }

        //Région,CGO,CM,Nom responsable,Prénom responsable,Email responsable,Mobile responsable,Nom et Prénom Superviseur,Mobile superviseur,Adresse,Email générique CGO
        $cgo
            ->setCm($arrayEntity['CM'])
            ->setName($arrayEntity['CGO'])
            ->setRegionErm($regionErm)
            ->setAddress($arrayEntity['Adresse'])
            ->setManager($manager)
            ->setClassErm($this->shopClassRepository->findOneByName($arrayEntity['Classe CGO']))
            ->setEmail($arrayEntity['Email générique CGO'])
            ->setZoneName($arrayEntity['ZoneCGO'])
            ->setTerritoryColor($this->mapsService->randomHexadecimalColor());

        return $cgo;
    }

    public function importShopsUnderControls(SymfonyStyle $io): void
    {
        $io->title('Importation des centres sous controle des CGO ERM');

            $totals = $this->readCsvFileShopsUnderControls();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateShopsUnderControls($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush();
            }
            

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function readCsvFileShopsUnderControls(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/annuaire.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }


    private function createOrUpdateShopsUnderControls(array $arrayEntity): Shop
    {

        $shop = $this->shopRepository->findOneByCm($arrayEntity['CM']);

        if(strlen($arrayEntity['Rattachement direct CGO VI']) > 1 && strlen($arrayEntity['Rattachement direct CGO VL']) == 1){

            $cgo = $this->cgoRepository->findOneCgoByZoneNameAndClasse($arrayEntity['Rattachement direct CGO VI'], 'VI');
            if($cgo instanceof Cgo){

                $shop->addCgo($cgo);

            }
            
        }else if(strlen($arrayEntity['Rattachement direct CGO VI']) == 1 && strlen($arrayEntity['Rattachement direct CGO VL']) > 1){

            $cgo = $this->cgoRepository->findOneCgoByZoneNameAndClasse($arrayEntity['Rattachement direct CGO VL'], 'VL');
            if($cgo instanceof Cgo){

                $shop->addCgo($cgo);
            }

        }else if(strlen($arrayEntity['Rattachement direct CGO VI']) > 1 && strlen($arrayEntity['Rattachement direct CGO VL']) > 1){
            $cgos = [];
            $cgos[] = $this->cgoRepository->findBy(['zoneName' => $arrayEntity['Rattachement direct CGO VL']]);
            $cgos[] = $this->cgoRepository->findBy(['zoneName' => $arrayEntity['Rattachement direct CGO VI']]);
        
            foreach($cgos as $array){
                foreach($array as $cgo){
                    if($cgo instanceof Cgo){
                        $shop->addCgo($cgo);
                    }
                }
            }

        }

        return $shop;
    }

    public function updateCgos(SymfonyStyle $io): void
    {
        $io->title('Mise à jour des CGOs');

            $totals = $this->readCsvFileForUpdate();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->update($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush();
            }
            

            $io->progressFinish();
        

        $io->success('Màj terminée');
    }

    private function readCsvFileForUpdate(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/cgoUpdated.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function update(array $arrayEntity): Cgo
    {

        $cgo = $this->cgoRepository->findOneByCm($arrayEntity['cm']);

        //"id","city_id","region_erm_id","name","address","territory_color","cm","zone_name","manager_id","class_erm_id","email","telematic_area_id"
        $cgo
            ->setCity($this->cityRepository->find($arrayEntity['city_id']));

        return $cgo;
    }

    /**
     * @param City $cityOfIntervention
     * @param Shop $shop
     * @return array
     */
    public function getDistancesBeetweenDepannageAndShop(City $cityOfIntervention, Shop $shop): array
    {
        $interventionLatitude = $cityOfIntervention->getLatitude();
        $interventionLongitude = $cityOfIntervention->getLongitude();
        
        $shopLatitude = $shop->getCity()->getLatitude();
        $shopLongitude = $shop->getCity()->getLongitude();

        // 1. Construct the API endpoint cleanly
        $endpoint = sprintf(
            'https://api.tomtom.com/routing/1/calculateRoute/%s,%s:%s,%s/json',
            $interventionLatitude,
            $interventionLongitude,
            $shopLatitude,
            $shopLongitude
        );

        // 2. Use a try-catch block for robust error handling
        try {
            $response = $this->client->request('GET', $endpoint, [
                'query' => [
                    'key' => $_ENV['TOMTOM_API_KEY'],
                ],
            ]);

            // If the request was not successful, it will throw an exception
            $response->getStatusCode();

            $array_reponse = $response->toArray();
            
            // 3. Add checks for missing data
            if (!isset($array_reponse['routes'][0]['summary'])) {
                // You can log this or return a specific error structure
                // For this example, we return a default failure array
                return [
                    'shop'     => $shop,
                    'distance' => null,
                    'duration' => null,
                    'error'    => 'TomTom API response missing route summary.'
                ];
            }

            $summary = $array_reponse['routes'][0]['summary'];

            return [
                'shop'     => $shop,
                'distance' => $summary['lengthInMeters'],
                'duration' => $summary['travelTimeInSeconds'],
            ];

        } catch (\Exception $e) {
            // Log the error
            // $this->logger->error('TomTom API request failed: ' . $e->getMessage());
            
            // Return an array with error information
            return [
                'shop'     => $shop,
                'distance' => null,
                'duration' => null,
                'error'    => 'TomTom API request failed: ' . $e->getMessage()
            ];
        }
    }

    public function getShopsByClassErmAndOptionArroundCityOfIntervention(City $cityOfIntervention, array $classErm, string $option, array $optionsTelematique = []): array
    {

        $datas = [];
        if($option == 'telematique'){
             // 1. On trouve d'abord les techniciens qui correspondent EXACTEMENT aux critères
            $formations = $optionsTelematique['formations'] ?? [];
            // $fonctions = $optionsTelematique['fonctions'] ?? [];
            // $vehicles = $optionsTelematique['vehicles'] ?? [];

            $formationsNames = $formations->map(fn($f) => $f->getName())->toArray();
            // $fonctionsNames = $fonctions->map(fn($f) => $f->getName())->toArray();
            // $vehiclesNames = $vehicles->map(fn($v) => $v->getName())->toArray();

            $matchingTechnicians = $this->technicianRepository->findAllTelematicTechnicians(
                $formationsNames);

            // 2. On utilise les techniciens trouvés pour chercher les boutiques
            $shops = $this->shopRepository->findShopsByTechnicians($matchingTechnicians);

        }else{
            $shops = $this->shopRepository->findShopsforDepannage($classErm);
        }
 
        $shopsByRayonOfIntervention = [];
        foreach($shops as $shop){
            if($this->distance($cityOfIntervention,$shop,'K', $_ENV['RAYON_OF_INTERVENTION']) == true){
                $shopsByRayonOfIntervention[] = $shop;
            }
        }

        foreach($shopsByRayonOfIntervention as $i => $shop){
            array_push($datas, $this->getDistancesBeetweenDepannageAndShop($cityOfIntervention,$shop));
            //on attend 1 seconde tous les 5 appels à l'api
            if($i > 0 && $i % 5 == 0){
                sleep(1);
            }
        }

        array_multisort(array_column($datas, 'distance'), SORT_ASC, $datas);

        return $datas;
    }

    private function distance(City $cityOfIntervention, Shop $shop, string $unit, int $rayonOfIntervention):bool 
    {
        $lat1 = $cityOfIntervention->getLatitude();
        $lon1 = $cityOfIntervention->getLongitude();
        $lat2 = $shop->getCity()->getLatitude();
        $lon2 = $shop->getCity()->getLongitude();

        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            $rayon = 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);
        
            if($unit == "K") {
                $rayon = ($miles * 1.609344);
            } else if ($unit == "N") {
                $rayon = ($miles * 0.8684);
            } else {
                $rayon =  $miles;
            }
        }

        if($rayon <= $rayonOfIntervention){
            return true;
        }else{
            return false;
        }
    }
}