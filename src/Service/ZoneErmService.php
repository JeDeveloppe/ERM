<?php

namespace App\Service;

use App\Entity\RegionErm;
use App\Entity\ZoneErm;
use App\Repository\RegionErmRepository;
use League\Csv\Reader;
use App\Repository\ZoneErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

class ZoneErmService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ZoneErmRepository $zoneErmRepository,
        private RegionErmRepository $regionErmRepository,
        private MapsService $mapsService
        ){
    }

    public function importZoneserm(SymfonyStyle $io): void
    {
        $io->title('Importation des zones ERM');

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
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/annuaire.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): ZoneErm
    {
        $regionErm = $this->regionErmRepository->findOneByName($arrayEntity['Région']);

        $zoneErm = $this->zoneErmRepository->findOneByName($arrayEntity['Secteur RA VL ou Zone MV']);

        if(!$zoneErm){
            $zoneErm = new ZoneErm();
        }

        $zoneErm->setName($arrayEntity['Secteur RA VL ou Zone MV'])->setRegionErm($regionErm)->setTerritoryColor($this->mapsService->randomHexadecimalColor());

        return $zoneErm;
    }

}