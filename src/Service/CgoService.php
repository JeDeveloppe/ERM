<?php

namespace App\Service;

use App\Entity\Cgo;
use App\Entity\City;
use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\Person;
use App\Entity\RoleErm;
use App\Entity\RegionErm;
use App\Repository\CgoRepository;
use App\Service\GeocodingService;
use App\Repository\CityRepository;
use App\Repository\ShopRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;
use App\Repository\ShopClassRepository;
use App\Repository\PersonRepository;
use App\Repository\RoleErmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CgoService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private EntityManagerInterface $em,
        private ShopClassRepository $shopClassRepository,
        private ZoneErmRepository $zoneErmRepository,
        private CityRepository $cityRepository,
        private CgoRepository $cgoRepository,
        private RegionErmRepository $regionErmRepository,
        private RoleErmRepository $roleErmRepository,
        private MapsService $mapsService,
        private ShopRepository $shopRepository,
        private HttpClientInterface $client,
        private PersonRepository $personRepository,
        private GeocodingService $geocodingService
        ){
    }

    public function importCgos(SymfonyStyle $io): void
    {
        $io->title('Importation des CGO ERM');

            $totals = $this->readCsvFile();
            $zoneAliasIndex = $this->buildZoneAliasIndex();

            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdate($arrayTotal, $zoneAliasIndex, $io);
                if ($entity !== null) {
                    $this->em->persist($entity);
                    $this->em->flush();
                }
            }


            $io->progressFinish();


        $io->success('Importation terminée');
    }

    private function readCsvFile(): Reader
    {
        $csv = Reader::createFromPath($this->projectDir . '/import/cgo.csv', 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    /**
     * cgo.csv n'a plus de colonne "Classe CGO"/"ZoneCGO" dédiée : la classe (VI/VL/MV) et la zone
     * se retrouvent dans le nom du CGO lui-même (ex: "CGO VI Clermont Lumière").
     *
     * @return array{0: string, 1: string} [classe, zone longue]
     */
    private function extractClassAndZoneFromName(string $cgoName): array
    {
        if (preg_match('/^CGO\s+(VI|VL|MV)\s+(.+)$/u', trim($cgoName), $matches)) {
            return [$matches[1], trim($matches[2])];
        }

        return ['', trim($cgoName)];
    }

    /**
     * centres.csv référence les CGO par un alias de zone court (ex: "Clermont", "Cesson")
     * dans ses colonnes "Rattachement direct CGO VI/VL". On construit ici l'index
     * normalisé -> [alias d'origine, région ERM canonique] pour :
     * - retrouver l'alias court à partir de la zone longue déduite du nom du CGO, afin que
     *   createOrUpdateShopsUnderControls() (qui fait un match exact sur Cgo.zoneName)
     *   continue de fonctionner ;
     * - déduire la vraie région ERM du CGO depuis centres.csv, plutôt que depuis la colonne
     *   "Région" de cgo.csv qui utilise un découpage commercial plus grossier ("AURA",
     *   "OUEST", "SUD"...) incompatible avec les régions ERM officielles et ambigu (ex:
     *   "AURA" regroupe à la fois des centres d'Auvergne et de Rhône-Alpes, deux régions
     *   distinctes dans centres.csv).
     *
     * @return array<string, array{alias: string, region: string}>
     */
    private function buildZoneAliasIndex(): array
    {
        $csv = Reader::createFromPath($this->projectDir . '/import/centres.csv', 'r');
        $csv->setHeaderOffset(0);

        $tokens = [];
        foreach ($csv->getRecords() as $row) {
            $region = trim($row['Région'] ?? '');
            foreach ([$row["Rattachement\ndirect CGO VI"] ?? '', $row["Rattachement\ndirect CGO VL"] ?? ''] as $alias) {
                $alias = trim($alias);
                if ($alias === '' || $alias === 'X') {
                    continue;
                }
                $normalized = $this->normalizeForMatching($alias);
                if (!isset($tokens[$normalized])) {
                    $tokens[$normalized] = ['alias' => $alias, 'region' => $region];
                }
            }
        }

        return $tokens;
    }

    private function normalizeForMatching(string $value): string
    {
        $value = str_replace('_', ' ', $value);
        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        if ($transliterator !== null) {
            $value = $transliterator->transliterate($value);
        }
        $value = preg_replace('/[^a-zA-Z0-9 ]/', '', $value) ?? $value;

        return mb_strtolower(trim($value));
    }

    /**
     * @return array{0: string, 1: ?string} [zoneName, région ERM canonique ou null si non trouvée]
     */
    private function resolveZoneAndRegion(string $longZone, array $zoneAliasIndex, SymfonyStyle $io, string $cgoName): array
    {
        $normalizedLongZone = $this->normalizeForMatching($longZone);

        foreach ($zoneAliasIndex as $normalizedAlias => $data) {
            if (str_contains($normalizedLongZone, $normalizedAlias) || str_contains($normalizedAlias, $normalizedLongZone)) {
                return [$data['alias'], $data['region'] !== '' ? $data['region'] : null];
            }
        }

        $io->warning('Aucun alias de zone trouvé dans centres.csv pour le CGO "' . $cgoName . '" (zone déduite: "' . $longZone . '") - le rattachement automatique des centres à ce CGO risque d\'échouer, et la région ERM ne peut pas être déduite');

        return [$longZone, null];
    }

    /**
     * cgo.csv n'a plus de code postal/ville dédiés : on les extrait de l'adresse complète
     * (le format observé termine toujours par "<code postal> <ville>", avec parfois un
     * espace au milieu du code postal, ex: "...63 100 Clermont-Ferrand").
     */
    private function extractCityFromAddress(string $address, SymfonyStyle $io): ?City
    {
        if (!preg_match('/(\d{2})\s?(\d{3})\s+([^,]+)$/u', trim($address), $matches)) {
            $io->warning('Impossible d\'extraire un code postal/ville depuis l\'adresse "' . $address . '"');

            return null;
        }

        $postalCode = $matches[1] . $matches[2];
        $cityName = trim($matches[3]);

        $city = $this->cityRepository->findBestMatch($postalCode, $cityName);

        if (!$city) {
            $io->warning('Ville introuvable pour code postal "' . $postalCode . '" et ville "' . $cityName . '" (adresse: "' . $address . '")');
        }

        return $city;
    }

    private function createOrUpdate(array $arrayEntity, array $zoneAliasIndex, SymfonyStyle $io): ?Cgo
    {

        [$class, $longZone] = $this->extractClassAndZoneFromName($arrayEntity['CGO']);
        [$zoneName, $regionName] = $this->resolveZoneAndRegion($longZone, $zoneAliasIndex, $io, $arrayEntity['CGO']);
        $city = $this->extractCityFromAddress($arrayEntity['Adresse'], $io);

        // Le responsable du CGO (rôle RCGO VI ou RCGO VL selon la classe du CGO).
        $rcgoRoleName = $class === 'VL' ? RoleErm::RCGO_VL : RoleErm::RCGO_VI;
        $manager = $this->personRepository->findOneBy(['name' => $arrayEntity['Nom responsable']]);
        if(!$manager){
            $manager = new Person();
            $manager->setFirstName('Prénom responsable')
                ->setName($arrayEntity['Nom responsable'])
                ->setEmail('Email responsable')
                ->setPhone('Mobile responsable');
            $this->em->persist($manager);
        }
        $rcgoRole = $this->roleErmRepository->findOneBy(['name' => $rcgoRoleName]);
        if ($rcgoRole) {
            $manager->addRole($rcgoRole);
        }
        $this->em->flush();

        $cgo = $this->cgoRepository->findOneByCm($arrayEntity['CM']);

        if(!$cgo){
            $cgo = new Cgo();
        }

        $regionErm = $cgo->getRegionErm();
        if ($regionName !== null) {
            $regionErm = $this->regionErmRepository->findOneByName($regionName);
            if (!$regionErm) {
                $regionErm = new RegionErm();
                $regionErm->setName($regionName);
                $this->em->persist($regionErm);
                $this->em->flush($regionErm);
            }
        }

        if (!$regionErm) {
            $io->warning('Aucune région ERM déterminable pour le CGO "' . $arrayEntity['CGO'] . '" - ligne ignorée');

            return null;
        }

        //Région,CGO,CM,Nom responsable,Prénom responsable,Email responsable,Mobile responsable,Nom et Prénom Superviseur,Mobile superviseur,Adresse,Email générique CGO,Animateur Prévention Santé Sécurité,Nom RRH,Gestionnaire paie
        $cgo
            ->setCm($arrayEntity['CM'])
            ->setName($arrayEntity['CGO'])
            ->setRegionErm($regionErm)
            ->setAddress($arrayEntity['Adresse'])
            ->setManager($manager)
            ->setClassErm($this->shopClassRepository->findOneByName($class))
            ->setEmail($arrayEntity['Email générique CGO'])
            ->setZoneName($zoneName)
            ->setCity($city)
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
        $csv = Reader::createFromPath($this->projectDir . '/import/centres.csv', 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }


    private function createOrUpdateShopsUnderControls(array $arrayEntity): Shop
    {

        $shop = $this->shopRepository->findOneByCm($arrayEntity['CM']);

        if(strlen($arrayEntity["Rattachement\ndirect CGO VI"]) > 1 && strlen($arrayEntity["Rattachement\ndirect CGO VL"]) == 1){

            $cgo = $this->cgoRepository->findOneCgoByZoneNameAndClasse($arrayEntity["Rattachement\ndirect CGO VI"], 'VI');
            if($cgo instanceof Cgo){

                $shop->addCgo($cgo);

            }

        }else if(strlen($arrayEntity["Rattachement\ndirect CGO VI"]) == 1 && strlen($arrayEntity["Rattachement\ndirect CGO VL"]) > 1){

            $cgo = $this->cgoRepository->findOneCgoByZoneNameAndClasse($arrayEntity["Rattachement\ndirect CGO VL"], 'VL');
            if($cgo instanceof Cgo){

                $shop->addCgo($cgo);
            }

        }else if(strlen($arrayEntity["Rattachement\ndirect CGO VI"]) > 1 && strlen($arrayEntity["Rattachement\ndirect CGO VL"]) > 1){
            $cgos = [];
            $cgos[] = $this->cgoRepository->findBy(['zoneName' => $arrayEntity["Rattachement\ndirect CGO VL"]]);
            $cgos[] = $this->cgoRepository->findBy(['zoneName' => $arrayEntity["Rattachement\ndirect CGO VI"]]);

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

    public function getShopsByClassErmAndOptionArroundCityOfIntervention(City $cityOfIntervention, array $classErm, string $option, array $optionsTelematique = []): array
    {

        $rayonOfIntervention = $_ENV['RAYON_OF_ROAD_INTERVENTION'];

        $datas = [];
        if($option == 'telematique'){
            $rayonOfIntervention = $_ENV['RAYON_OF_TELEMATIC_INTERVENTION'];
             // 1. On trouve d'abord les techniciens qui correspondent EXACTEMENT aux critères
            $formations = $optionsTelematique['formations'] ?? [];
            // $fonctions = $optionsTelematique['fonctions'] ?? [];
            // $vehicles = $optionsTelematique['vehicles'] ?? [];

            $formationsNames = $formations->map(fn($f) => $f->getName())->toArray();
            // $fonctionsNames = $fonctions->map(fn($f) => $f->getName())->toArray();
            // $vehiclesNames = $vehicles->map(fn($v) => $v->getName())->toArray();

            $matchingPeople = $this->personRepository->findAllTelematicPeople(
                $formationsNames);

            // 2. On utilise les techniciens trouvés pour chercher les boutiques
            $shops = $this->shopRepository->findShopsByPeople($matchingPeople);

        }else{
            $shops = $this->shopRepository->findShopsforDepannage($classErm);
        }

        $shopsByRayonOfIntervention = [];
        foreach($shops as $shop){
            if(!$shop->getCity()){
                continue;
            }
            if($this->geocodingService->testDistance($cityOfIntervention,$shop,'K', $rayonOfIntervention) == true){
                $shopsByRayonOfIntervention[] = $shop;
            }
        }

        foreach($shopsByRayonOfIntervention as $i => $shop){
            array_push($datas, $this->geocodingService->getDistancesBeetweenDepannageAndShopWithOSRM($cityOfIntervention,$shop));
            //on attend 1 seconde tous les 5 appels à l'api
            // if($i > 0 && $i % 5 == 0){
            //     sleep(1);
            // }
        }

        array_multisort(array_column($datas, 'distance'), SORT_ASC, $datas);

        return $datas;
    }
}
