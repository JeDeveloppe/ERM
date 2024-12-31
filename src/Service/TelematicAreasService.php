<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\TelematicArea;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TelematicAreaRepository;

use Symfony\Component\Console\Style\SymfonyStyle;

class TelematicAreasService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TelematicAreaRepository $TelematicAreaRepository,
        private ShopRepository $shopRepository
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
            }
            
            $this->em->flush();

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function readCsvFile(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/TelematicAreas.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): TelematicArea
    {
        $cgoArea = $this->TelematicAreaRepository->findOneBy(['cgoWhoControlsArea' => $this->shopRepository->findOneBy(['id' => $arrayEntity['cgo_who_controls_area_id']])]);

        if(!$cgoArea){
            $cgoArea = new TelematicArea();
        }

        //"id","cgo_who_controls_area_id","zone_color"
        $cgoArea
            ->setCgoWhoControlsArea($this->shopRepository->findOneBy(['id' => $arrayEntity['cgo_who_controls_area_id']]))
            ->setZoneColor($arrayEntity['zone_color']);


        return $cgoArea;
    }

}