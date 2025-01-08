<?php

namespace App\Service;

use App\Entity\Cgo;
use League\Csv\Reader;
use App\Entity\TelematicArea;
use App\Repository\CgoRepository;
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
        private MapsService $mapsService
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

}