<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\Department;
use App\Entity\CgoTelematicArea;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LargeRegionRepository;
use App\Repository\CgoTelematicAreaRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

class DepartmentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DepartmentRepository $departmentRepository,
        private LargeRegionRepository $largeregionRepository,
        private SluggerInterface $sluggerInterface,
        ){
    }

    public function importDepartementsFrancais(SymfonyStyle $io): void
    {
        $io->title('Importation des départements Francais');

        $departements = $this->readCsvFileDepartementsFrancais();
        
        $io->progressStart(count($departements));

        foreach($departements as $arrayDepartement){
            $io->progressAdvance();
            $departement = $this->createOrUpdateDepartmentFrancais($arrayDepartement);
            $this->em->persist($departement);
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Importation des départements terminé');
    }

    //lecture des fichiers exportes dans le dossier import
    private function readCsvFileDepartementsFrancais(): Reader
    {
        $csvDepartement = Reader::createFromPath('%kernel.root.dir%/../import/departments.csv','r');
        $csvDepartement->setHeaderOffset(0);

        return $csvDepartement;
    }

    private function createOrUpdateDepartmentFrancais(array $arrayDepartement): Department
    {
        $departement = $this->departmentRepository->findOneBy(['code' => $arrayDepartement['code'], 'name' => $arrayDepartement['name']]);

        if(!$departement){
            $departement = new Department();
        }

        //"id","large_region_id","telematic_area_id","name","slug","code","simplemap_code"

        $departement
            ->setName($arrayDepartement['name'])
            ->setCode(strval($arrayDepartement['code']))
            ->setSlug($this->sluggerInterface->slug($arrayDepartement['slug']))
            ->setSimplemapCode($arrayDepartement['simplemap_code'])
            ->setLargeregion($this->largeregionRepository->findOneBy(['id' => $arrayDepartement['large_region_id'] ]));

        return $departement;
    }

    public function importDepartmentsWithGpsPoints(SymfonyStyle $io): void
    {
        $io->title('Importation des points GPS des départements Francais');
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
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/departmentsBordures.csv','r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $array): Department
    {
        $department = $this->departmentRepository->findOneByCode($array['Code INSEE Département']);

        if($department){
            $datas = $array['geo_shape'];
            $data = json_decode($datas, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                dd( "Erreur lors du décodage JSON : " . json_last_error_msg());
            }
            $department->setGpsPoints($data);
        }else{
            dump('no department for ' . $array['Code Officiel Courant Departement']);
        }


        return $department;
    }
}