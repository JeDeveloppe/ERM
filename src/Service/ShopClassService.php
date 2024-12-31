<?php

namespace App\Service;

use App\Entity\RegionErm;
use App\Entity\ShopClass;
use App\Entity\ZoneErm;
use App\Repository\RegionErmRepository;
use App\Repository\ShopClassRepository;
use League\Csv\Reader;
use App\Repository\ZoneErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

class ShopClassService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopClassRepository $shopClassRepository
        ){
    }

    public function importShopClasses(SymfonyStyle $io): void
    {
        $io->title('Importation des classes ERM');

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

    private function createOrUpdate(array $array): ShopClass
    {

        $shopClass = $this->shopClassRepository->findOneByName($array['Classe']);

        if(!$shopClass){
            $shopClass = new ShopClass();
        }

        $shopClass->setName($array['Classe']);

        return $shopClass;
    }

}