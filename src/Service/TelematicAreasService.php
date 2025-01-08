<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\TelematicArea;
use App\Repository\CgoRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TelematicAreaRepository;

use Symfony\Component\Console\Style\SymfonyStyle;

class TelematicAreasService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TelematicAreaRepository $TelematicAreaRepository,
        private CgoRepository $cgoRepository,
        private MapsService $mapsService
        ){
    }

    public function importCgoTelematicAreas(SymfonyStyle $io): void
    {
        $io->title('Importation des zones télématic');

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

    private function createOrUpdate(array $arrayEntity): TelematicArea
    {
        $cgoArea = $this->TelematicAreaRepository->findOneByCgo($arrayEntity['CM']);

        if(!$cgoArea){
            $cgoArea = new TelematicArea();
        }

        //"id","cgo_who_controls_area_id","zone_color"
        $cgoArea
            ->setCgo($this->cgoRepository->findOneByCm($arrayEntity['CM']))
            ->setTerritoryColor($this->mapsService->randomHexadecimalColor());

        return $cgoArea;
    }

}