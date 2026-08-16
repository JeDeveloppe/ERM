<?php

namespace App\Service;

use App\Entity\RegionErm;
use App\Entity\ZoneErm;
use App\Repository\RegionErmRepository;
use League\Csv\Reader;
use App\Repository\ZoneErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ZoneErmService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
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
        $csv = Reader::createFromPath($this->projectDir . '/import/centres.csv', 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): ZoneErm
    {
        $regionName = trim($arrayEntity['Région']);

        $regionErm = $this->regionErmRepository->findOneByName($regionName);
        if (!$regionErm) {
            // Sécurité : normalement déjà créée par RegionErmService qui tourne avant dans le
            // pipeline, mais on ne veut pas planter/laisser une zone sans région si l'ordre change.
            $regionErm = new RegionErm();
            $regionErm->setName($regionName)->setTerritoryColor($this->mapsService->randomHexadecimalColor());
            $this->em->persist($regionErm);
            $this->em->flush($regionErm);
        }

        $zoneErm = $this->zoneErmRepository->findOneByName($arrayEntity['Zone']);

        if(!$zoneErm){
            $zoneErm = new ZoneErm();
        }

        $zoneErm->setName($arrayEntity['Zone'])->setRegionErm($regionErm)->setTerritoryColor($this->mapsService->randomHexadecimalColor());

        return $zoneErm;
    }

}