<?php

namespace App\Service;

use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\ZoneErm;
use App\Entity\RegionErm;
use App\Repository\CgoRepository;
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
        private CityRepository $cityRepository,
        private CgoRepository $cgoRepository
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

        if(!$shop){
            $shop = new Shop();
        }

        $manager = $this->managerRepository->findOneByEmail($arrayEntity['email']) ?? $this->managerRepository->findOneByEmail('manager.inconnu@euromaster.com');

        if(!$manager){
            dd('no manager for ' . $arrayEntity['email']);
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention
        $shop
            ->setCm($arrayEntity['CM'])
            ->setName($arrayEntity['Libelle Centre'])
            ->setZoneErm($this->zoneErmRepository->findOneByName($arrayEntity['Secteur RA VL ou Zone MV']))
            ->setShopClass($this->shopClassRepository->findOneByName($arrayEntity['Classe']))
            ->setAddress($arrayEntity['Adresse'])
            ->setPhone($arrayEntity['Ligne pour les clients(diffusion OK)'])
            ->setManager($manager)
            ->setCity($this->cityRepository->findOneBy(['postalCode' => $arrayEntity['Code Postal'], 'name' => $arrayEntity['Ville']]));

        return $shop;
    }

    public function updateShops(SymfonyStyle $io): void
    {
        $io->title('Mise à jour des Centres ERM');

            $totals = $this->readCsvFileForUpdate();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateShops($arrayTotal);
                $this->em->persist($entity);
            }
            $this->em->flush();
            

            $io->progressFinish();
        

        $io->success('Mise à jour terminée');
    }

    private function readCsvFileForUpdate(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/shops.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdateShops(array $arrayEntity): Shop
    {
        //"id","zone_erm_id","shop_class_id","city_id","manager_id","cm","name","address","phone"
        
        $shop = $this->shopRepository->findOneByCm($arrayEntity['cm']);

        if(!$shop){
            $shop = new Shop();
        }

        $shop
            ->setCity($this->cityRepository->findOneById($arrayEntity['city_id']));

        return $shop;
    }
}