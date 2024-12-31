<?php

namespace App\Service;

use App\Entity\City;
use League\Csv\Reader;
use App\Repository\CityRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

class CityService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CityRepository $cityRepository,
        private DepartmentRepository $departmentRepository,
        private SluggerInterface $sluggerInterface
        ){
    }

    public function importCitiesOfFrance(SymfonyStyle $io): void
    {
        $io->title('Importation des villes Françaises');

            $cities = $this->readCsvFileFrance();
            
            $io->progressStart(count($cities));

            foreach($cities as $arrayVille){

                $io->progressAdvance();
                $ville = $this->createOrUpdateCityFrance($arrayVille);
                $this->em->persist($ville);
            }
            
            $this->em->flush();

            unset($cities);
            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function readCsvFileFrance(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/cities.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdateCityFrance(array $arrayVille): City
    {
        $city = $this->cityRepository->findOneBy([
            'inseeCode' => $arrayVille['insee_code'],
            'postalCode' => $arrayVille['zip_code'],
            'name' => $arrayVille["name"]
        ]);

        if(!$city){
            $city = new City();
        }

        // id,department_code,insee_code,zip_code,name,slug,gps_lat,gps_lng

        $city->setName($arrayVille['name'])
            ->setLatitude($arrayVille['gps_lat'])
            ->setLongitude($arrayVille['gps_lng'])
            ->setPostalcode($arrayVille['zip_code'])
            ->setSlug($this->sluggerInterface->slug($arrayVille['name']))
            ->setDepartment($this->departmentRepository->findOneBy(['code' => $arrayVille['department_code']]) ?? $this->departmentRepository->findOneBy(['code' => 38]))
            ->setInseeCode($arrayVille['insee_code']);

        return $city;
    }

}