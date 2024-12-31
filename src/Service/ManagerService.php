<?php

namespace App\Service;

use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\Manager;
use App\Repository\ShopRepository;
use App\Repository\ManagerRepository;
use App\Repository\ZoneErmRepository;
use App\Repository\RegionErmRepository;

use App\Repository\ShopClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ManagerService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ManagerRepository $managerRepository
        ){
    }

    public function importManagers(SymfonyStyle $io): void
    {
        $io->title('Importation des managers ERM');

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

    private function createOrUpdate(array $arrayEntity): Manager
    {
        $manager = $this->managerRepository->findOneByEmail($arrayEntity['email']);

        if(!$manager){
            $manager = new Manager();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $manager
            ->setFirstName($arrayEntity['Prénom RCS'])
            ->setLastName($arrayEntity['Nom RCS'])
            ->setEmail($arrayEntity['email'])
            ->setPhone($arrayEntity['Tél mobile resp.']);

        return $manager;
    }

}