<?php

namespace App\Service;

use App\Entity\ManagerClass;
use App\Entity\RegionErm;
use App\Entity\ZoneErm;
use App\Repository\ManagerClassRepository;
use App\Repository\RegionErmRepository;
use League\Csv\Reader;
use App\Repository\ZoneErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

class ManagerClassService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ManagerClassRepository $managerClassRepository,
        ){
    }

    public function importManagerClass(SymfonyStyle $io): void
    {
        $io->title('Importation des classes de managers ERM');

            $totals = ['AO','RCS','DR','RAVL','RZ'];
        
            $io->progressStart(count($totals));

            foreach($totals as $total){

                $io->progressAdvance();

                $managerClass = $this->managerClassRepository->findOneBy(['name' => $total]);

                if(!$managerClass){
                    $managerClass = new ManagerClass();
                }

                $managerClass->setName($total);
                $this->em->persist($managerClass);
                $this->em->flush();
            }
            

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

}