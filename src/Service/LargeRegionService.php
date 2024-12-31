<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\Largeregion;
use App\Entity\Granderegion;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LargeRegionRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

class LargeRegionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $sluggerInterface,
        private LargeRegionRepository $largeregionRepository,
        ){
    }

    public function importLargesregions(SymfonyStyle $io): void
    {
        $io->title('Importation des grandes régions');

        $regions = $this->readCsvFileRegionsFrancaise();
        
        $io->progressStart(count($regions));

        foreach($regions as $arrayRegion){
            $io->progressAdvance();
            $region = $this->createOrUpdateRegionFrancaise($arrayRegion);
            $this->em->persist($region);
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Importation des régions terminé');
    }

    //lecture des fichiers exportes dans le dossier import
    private function readCsvFileRegionsFrancaise(): Reader
    {
        $csvregion = Reader::createFromPath('%kernel.root.dir%/../import/largeregions.csv','r');
        $csvregion->setHeaderOffset(0);

        return $csvregion;
    }

    private function createOrUpdateRegionFrancaise(array $arrayRegion): Largeregion
    {
        $region = $this->largeregionRepository->findOneBy(['code' => $arrayRegion['code']]);

        if(!$region){
            $region = new Largeregion();
        }

        $region->setName($arrayRegion['name'])
            ->setCode($arrayRegion['code'])
            ->setSlug($this->sluggerInterface->slug($arrayRegion['slug']));

        return $region;
    }

}