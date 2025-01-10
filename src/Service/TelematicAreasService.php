<?php

namespace App\Service;

use App\Entity\Cgo;
use App\Entity\Department;
use League\Csv\Reader;
use App\Entity\TelematicArea;
use App\Repository\CgoRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ShopClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TelematicAreaRepository;

use Symfony\Component\Console\Style\SymfonyStyle;

class TelematicAreasService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TelematicAreaRepository $telematicAreaRepository,
        private ShopClassRepository $shopClassRepository,
        private CgoRepository $cgoRepository,
        private MapsService $mapsService,
        private DepartmentRepository $departmentRepository
        ){
    }

    public function importCgoTelematicAreas(SymfonyStyle $io): void
    {
        $io->title('Importation des zones télématic');

            $cgos = $this->cgoRepository->findBy(['classErm' => $this->shopClassRepository->findOneBy(['name' => 'MV'])]);
        
            $io->progressStart(count($cgos));

            foreach($cgos as $cgo){

                $io->progressAdvance();
                $entity = $this->createOrUpdate($cgo);
                $this->em->persist($entity);
                $this->em->flush();
            }
            

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function createOrUpdate(Cgo $entity): TelematicArea
    {
        $cgoArea = $this->telematicAreaRepository->findOneByCgo($entity);

        if(!$cgoArea){
            $cgoArea = new TelematicArea();
        }

        //"id","cgo_who_controls_area_id","zone_color"
        $cgoArea
            ->setCgo($this->cgoRepository->find($entity))
            ->setTerritoryColor($this->mapsService->randomHexadecimalColor());

        return $cgoArea;
    }

    public function importDepartmentsInTelematicsAreas(SymfonyStyle $io): void
    {
        $io->title('Importation des départements dans les zones télématiques');

            $totals = $this->readCsvFileDepartments();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateDepartment($arrayTotal);
                $this->em->persist($entity);
            }
            $this->em->flush();

            $io->progressFinish();
        
        $io->success('Importation terminée');
    }

    private function readCsvFileDepartments(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/departments.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdateDepartment(array $arrayEntity): Department
    {
        $department = $this->departmentRepository->findOneByCode($arrayEntity['code']);

        if(!$department){
            $department = new Department();
        }

        //"id","large_region_id","telematic_area_id","name","slug","code","simplemap_code"
        $department
            ->setTelematicArea($this->telematicAreaRepository->find($arrayEntity['telematic_area_id']));

        return $department;
    }
}