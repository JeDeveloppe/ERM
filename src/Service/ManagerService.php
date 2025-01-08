<?php

namespace App\Service;

use App\Entity\Shop;
use League\Csv\Reader;
use App\Entity\Manager;
use App\Repository\ManagerClassRepository;
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
        private ManagerRepository $managerRepository,
        private ManagerClassRepository $managerClassRepository,
        private ShopRepository $shopRepository
        ){
    }

    public function importRcsManagers(SymfonyStyle $io): void
    {
        $io->title('Importation des managers ERM [RCS]');

            $totals = $this->readCsvFile();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateRCS($arrayTotal);
                $this->em->persist($entity);
            }
            $this->em->flush($entity);

            //on cré un manager générique au cas ou il en manque un
            $this->createOrUpdateFakeManager('manager.inconnu@euromaster.com', 'RCS');
            $io->progressFinish();
        
        $io->success('Importation terminée');
    }

    private function readCsvFile(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/annuaire.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdateRCS(array $arrayEntity): Manager
    {
        $manager = $this->managerRepository->findOneByEmail($arrayEntity['email']);

        if(!$manager){
            $manager = new Manager();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $manager
            ->setFirstName($arrayEntity['Prénom RCS'] ?? 'A DEFINIR')
            ->setLastName($arrayEntity['Nom RCS'] ?? 'A DEFINIR')
            ->setEmail($arrayEntity['email'])
            ->setManagerClass($this->managerClassRepository->findOneByName('RCS'))
            ->setPhone($arrayEntity['Tél mobile resp.']);

        return $manager;
    }

    public function importDrManagers(SymfonyStyle $io): void
    {
        $io->title('Importation des managers ERM [DR]');

            $totals = $this->readCsvFile();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateDR($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush($entity);
            }

            $io->progressFinish();
        
        $io->success('Importation terminée');
    }

    private function createOrUpdateDR(array $arrayEntity): Manager
    {
        $manager = $this->managerRepository->findOneByLastName($arrayEntity['Nom DR']);

        if(!$manager){
            $manager = new Manager();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $manager
            ->setFirstName('A définir')
            ->setLastName($arrayEntity['Nom DR'])
            ->setManagerClass($this->managerClassRepository->findOneByName('DR'))
            ->setEmail('A définir')
            ->setPhone('A définir');

        return $manager;
    }

    public function importRAVL_RZManagers(SymfonyStyle $io): void
    {
        $io->title('Importation des managers ERM [RAVL et RZ]');

            $totals = $this->readCsvFile();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateRAVL_RZ($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush($entity);
            }

            $io->progressFinish();
        
        $io->success('Importation terminée');
    }

    private function createOrUpdateRAVL_RZ(array $arrayEntity): Manager
    {
        $manager = $this->managerRepository->findOneByLastName($arrayEntity['Nom RA VL ou Nom R. Zone']);

        if(!$manager){
            $manager = new Manager();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $manager
            ->setFirstName('A définir')
            ->setLastName($arrayEntity['Nom RA VL ou Nom R. Zone'])
            ->setEmail('A définir')
            ->setPhone('A définir');
        
        if(preg_match('/RAVL/', $arrayEntity['Nom RA VL ou Nom R. Zone'])){
            $manager
                ->setManagerClass($this->managerClassRepository->findOneByName('RAVL'));
        }elseif (preg_match('/RZ/', $arrayEntity['Nom RA VL ou Nom R. Zone'])) {
            $manager
                ->setManagerClass($this->managerClassRepository->findOneByName('RZ'));
        }

        return $manager;
    }

    public function importAOManagers(SymfonyStyle $io): void
    {
        $io->title('Importation des managers ERM [AO]');

            $totals = $this->readCsvFile();
        
            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdateAO($arrayTotal);
                $this->em->persist($entity);
                $this->em->flush($entity);
            }

            $io->progressFinish();
        
        $io->success('Importation terminée');
    }

    private function createOrUpdateAO(array $arrayEntity): Manager
    {
        $manager = $this->managerRepository->findOneByLastName($arrayEntity['Nom AO']);

        if(!$manager){
            $manager = new Manager();
        }

       // Région,Nom DR,Secteur RA VL ou Zone MV,Nom RA VL ou Nom R. Zone,Nom AO,Libelle CM regroupés,CM,Libelle Centre,Statut,Classe,Nb EAD,"Rattachement direct CGO VI","Rattachement direct CGO VL",Nom RCS,Prénom RCS,email,"Ligne pour les clients(diffusion OK)",Tél mobile resp.,"Ligne directe centre (ne pas diffuser aux clients)",Adresse,Code Postal,Ville,"Animateur Prévention

        $manager
            ->setFirstName('A définir')
            ->setLastName($arrayEntity['Nom AO'])
            ->setEmail('A définir')
            ->setPhone('A définir')
            ->setManagerClass($this->managerClassRepository->findOneByName('AO'));

        return $manager;
    }

    public function createOrUpdateFakeManager(string $email, string $managerClass){

        $manager = $this->managerRepository->findOneByEmail($email);

        if(!$manager){
            $manager = new Manager();
        }

        $manager
            ->setFirstName('A définir')
            ->setLastName('A définir')
            ->setEmail($email)
            ->setPhone('A définir')
            ->setManagerClass($this->managerClassRepository->findOneByName($managerClass));

        $this->em->persist($manager);
        $this->em->flush($manager);
    }
}