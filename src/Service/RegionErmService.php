<?php

namespace App\Service;

use App\Entity\RegionErm;
use League\Csv\Reader;
use App\Repository\RegionErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RegionErmService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private EntityManagerInterface $em,
        private RegionErmRepository $regionErmRepository,
        private MapsService $mapsService
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
        $csv = Reader::createFromPath($this->projectDir . '/import/centres.csv', 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): RegionErm
    {
        $name = trim($arrayEntity['Région']);

        $regionErm = $this->regionErmRepository->findOneByName($name);

        if(!$regionErm){
            $regionErm = new RegionErm();
        }

        // La couleur n'est tirée que si la région n'en a pas déjà une (voir
        // ZoneErmService pour le même correctif) pour ne pas casser
        // l'espacement des couleurs entre régions à chaque réimport.
        if(!$regionErm->getTerritoryColor()){
            $regionErm->setTerritoryColor($this->mapsService->randomHexadecimalColor('region'));
        }

        $regionErm->setName($name);

        return $regionErm;
    }

}