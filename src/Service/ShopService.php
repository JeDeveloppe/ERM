<?php

namespace App\Service;

use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\ZoneErm;
use App\Entity\RegionErm;
use App\Repository\CityRepository;
use App\Repository\ManagerRepository;
use App\Repository\ShopRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;
use App\Repository\ShopClassRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShopService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopRepository $shopRepository,
        private ShopClassRepository $shopClassRepository,
        private ZoneErmRepository $zoneErmRepository,
        private ManagerRepository $managerRepository,
        private CityRepository $cityRepository
        ){
    }

    public function importShops(SymfonyStyle $io): void
    {
        $io->title('Importation des Centres ERM');

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

    private function createOrUpdate(array $arrayEntity): Shop
    {
        $shop = $this->shopRepository->findOneByCm($arrayEntity['CM']);
        
        $manager = $this->managerRepository->findOneByEmail($arrayEntity['email']);
        $zoneErm = $this->zoneErmRepository->findOneByName($arrayEntity['Secteur RA VL ou Zone MV']);
        $shopClass = $this->shopClassRepository->findOneByName($arrayEntity['Classe']);
        $city = $this->cityRepository->findOneBy(['postalCode' => $arrayEntity['Code Postal'], 'name' => $arrayEntity['Ville']]);

        if(!$shop){
            $shop = new Shop();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $shop
            ->setCm($arrayEntity['CM'])
            ->setName($arrayEntity['Libelle Centre'])
            ->setZoneErm($zoneErm)
            ->setShopClass($shopClass)
            ->setAddress($arrayEntity['Adresse'])
            ->setPhone($arrayEntity['Ligne pour les clients(diffusion OK)'])
            ->setManager($manager)
            ->setCity($city);

        return $shop;
    }

}