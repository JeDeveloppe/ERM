<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\Technician;
use App\Entity\Largeregion;
use App\Entity\Granderegion;
use App\Entity\Shop;
use App\Entity\TechnicianFormations;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LargeRegionRepository;
use App\Repository\ShopRepository;
use App\Repository\TechnicianFormationsRepository;
use App\Repository\TechnicianVehicleRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

class TechnicianService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopRepository $shoprepository,
        private TechnicianRepository $technicianRepository,
        private TechnicianFormationsRepository $technicianFormationsRepository,
        private TechnicianVehicleRepository $technicianVehicleRepository
        ){
    }

    public function importTechnicians(SymfonyStyle $io): void
    {
        $io->title('Importation des techniciens');

        $technicians = $this->readCsvFileTechnicians();
        
        $io->progressStart(count($technicians));

        foreach($technicians as $array){
            $io->progressAdvance();
            $technician = $this->createOrUpdateTechnician($array);
            $this->em->persist($technician);
            $this->em->flush();
        }


        $io->progressFinish();
        $io->success('Importation des techniciens terminé');
    }

    //lecture des fichiers exportes dans le dossier import
    private function readCsvFileTechnicians(): Reader
    {
        $csvtechnicians = Reader::createFromPath('%kernel.root.dir%/../import/technicians.csv','r');
        $csvtechnicians->setHeaderOffset(0);

        return $csvtechnicians;
    }

    private function createOrUpdateTechnician(array $arrayTechnician): Technician
    {
        $technician = $this->technicianRepository->findOneBy(['email' => $arrayTechnician['Email TM']]);

        if(!$technician){
            $technician = new Technician();
        }

        //?on recupere le shop du technicien
        $shop = $this->shoprepository->findOneBy(['cm' => $arrayTechnician['CM']]);

        //?on recupere le cgo ratacher au shop (peut etre null)
        $cgo = $shop->getCgos()->first();
        if(!$cgo){
            $cgo = null;
        }

        $nameAndFirstName = explode(" ",$arrayTechnician['Télématique VI TM1']);
        $technicianName = $nameAndFirstName[0];
        $technicianFirstName = $nameAndFirstName[1];

        $technician
            ->setEmail($arrayTechnician['Email TM'])
            ->setName($technicianName)
            ->setIsTelematic(true)
            ->setControledByCgo($cgo)
            ->setFirstName($technicianFirstName)
            ->setPhone($arrayTechnician['Téléphone Portable'])
            ->setInformations($arrayTechnician['INFOS DIVERS'])
            ->setShop($shop)
            ->setVehicle($this->technicianVehicleRepository->findOneBy(['name' => $arrayTechnician['Véhicule']]) ?? $this->technicianVehicleRepository->findOneBy(['name' => 'SANS VEHICULE']))
            ;

        $formations = $this->technicianFormationsRepository->findAll();

        foreach($formations as $formation){
            $this->testFormationIsOkorKo($formation->getName(), $arrayTechnician[$formation->getName()], $technician);
        }

        return $technician;

    }

    private function testFormationIsOkorKo(string $formationName, string $okOrKo, Technician $technician)
    {
        if($okOrKo === 'OK'){
            $technician->addTechnicianFormation($this->technicianFormationsRepository->findOneBy(['name' => $formationName]));
        }
    }
}