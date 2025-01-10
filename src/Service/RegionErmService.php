<?php

namespace App\Service;

use App\Entity\RegionErm;
use League\Csv\Reader;
use App\Repository\RegionErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

class RegionErmService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RegionErmRepository $regionErmRepository
        ){
    }

    public function importRegionserm(SymfonyStyle $io): void
    {
        $io->title('Importation des régions ERM');

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

    private function createOrUpdate(array $arrayEntity): RegionErm
    {
        $regionErm = $this->regionErmRepository->findOneByName($arrayEntity['Région']);

        if(!$regionErm){
            $regionErm = new RegionErm();
        }

        $regionErm->setName($arrayEntity['Région']);

        return $regionErm;
    }

}