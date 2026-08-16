<?php

namespace App\Service;

use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\ZoneErm;
use App\Entity\RegionErm;
use App\Repository\CgoRepository;
use App\Repository\CityRepository;
use App\Repository\ShopRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;
use App\Repository\ShopClassRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ShopService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private EntityManagerInterface $em,
        private ShopRepository $shopRepository,
        private ShopClassRepository $shopClassRepository,
        private ZoneErmRepository $zoneErmRepository,
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
        $csv = Reader::createFromPath($this->projectDir . '/import/centres.csv', 'r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $arrayEntity): Shop
    {
        $shop = $this->shopRepository->findOneByCm($arrayEntity['CM']);

        if(!$shop){
            $shop = new Shop();
        }

       // Région,Nom DR,Zone,Nom R. Zone ou RA VL,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients (diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,Animateur Prévention Santé Sécurité,Nom_RRH,Nom_GRH
        // Le "manager" du centre (RCS) est désormais un Person avec le rôle RCS
        // (cf. PersonService::importRCS), pas une entité Manager - le contact du
        // centre se récupère via Shop::getRcsPerson().
        $shop
            ->setCm($arrayEntity['CM'])
            ->setName($arrayEntity['Libelle Centre'])
            ->setZoneErm($this->zoneErmRepository->findOneByName($arrayEntity['Zone']))
            ->setShopClass($this->shopClassRepository->findOneByName($arrayEntity['Classe']))
            ->setAddress($arrayEntity['Adresse'])
            ->setPhone($arrayEntity["Ligne pour les clients\n(diffusion OK)"])
            ->setCity($this->cityRepository->findBestMatch($arrayEntity['Code Postal'], $arrayEntity['Ville']));

        return $shop;
    }
}
