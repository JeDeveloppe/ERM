<?php

namespace App\Service;

use App\Entity\City;
use League\Csv\Reader;
use App\Repository\CityRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CityService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private EntityManagerInterface $em,
        private CityRepository $cityRepository,
        private DepartmentRepository $departmentRepository,
        private SluggerInterface $sluggerInterface
        ){
    }

    public function importCitiesOfFrance(SymfonyStyle $io): void
    {
        $io->title('Importation des villes Françaises');

            $cities = iterator_to_array($this->readCsvFileFrance()->getRecords());

            if ($this->isAlreadyImported($cities)) {
                $io->success('Villes déjà importées (première et dernière ligne du CSV trouvées en base) - import ignoré');
                return;
            }

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

    /**
     * cities.csv contient ~36 000 lignes de données géographiques françaises qui ne
     * changent quasiment jamais. Plutôt que de refaire 36 000 lectures/écritures à
     * chaque `app:initdatabase`, on vérifie juste que la première et la dernière
     * ligne du CSV existent déjà en base : si oui, on considère l'import à jour.
     */
    private function isAlreadyImported(array $cities): bool
    {
        if (empty($cities)) {
            return true;
        }

        foreach ([reset($cities), end($cities)] as $row) {
            $exists = $this->cityRepository->findOneBy([
                'inseeCode' => $row['insee_code'],
                'postalCode' => $row['zip_code'],
                'name' => $row['name'],
            ]);

            if (!$exists) {
                return false;
            }
        }

        return true;
    }

    private function readCsvFileFrance(): Reader
    {
        $csv = Reader::createFromPath($this->projectDir . '/import/cities.csv', 'r');
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
            ->setPostalcode(strval($arrayVille['zip_code']))
            ->setSlug($this->sluggerInterface->slug($arrayVille['name']))
            ->setDepartment($this->departmentRepository->findOneBy(['code' => $arrayVille['department_code']]))
            ->setInseeCode(strval($arrayVille['insee_code']));

        return $city;
    }

}
