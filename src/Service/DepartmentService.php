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

        // "id","largeregion_id","cgo_telematic_area_id","name","slug","code","simplemap_code"

        $departement->setName($arrayDepartement['name'])
                ->setCode(strval($arrayDepartement['code']))
                ->setSlug($this->sluggerInterface->slug($arrayDepartement['slug']))
                ->setSimplemapCode($arrayDepartement['simplemap_code'])
                ->setLargeregion($this->largeregionRepository->findOneBy(['id' => $arrayDepartement['largeregion_id'] ]));

        return $departement;
    }
}