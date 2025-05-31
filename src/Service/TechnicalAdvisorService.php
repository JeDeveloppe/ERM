<?php

namespace App\Service;

use App\Entity\Manager;
use App\Entity\ShopClass;
use App\Entity\TechnicalAdvisor;
use App\Repository\ManagerClassRepository;
use App\Repository\ManagerRepository;
use App\Repository\ShopRepository;
use App\Repository\TechnicalAdvisorRepository;
use League\Csv\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TechnicalAdvisorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TechnicalAdvisorRepository $technicalAdvisorRepository,
        private ManagerRepository $managerRepository,
        private ManagerClassRepository $managerClassRepository,
        private ShopRepository $shopRepository,
        private MapsService $mapService
        ){
    }

    public function importCTs(SymfonyStyle $io): void
    {
        $io->title('Importation des CT');

            $totals = $this->readCsvFile();

            $io->progressStart(count($totals));

            foreach($totals as $arrayTotal){

                $io->progressAdvance();
                $entity = $this->createOrUpdate($arrayTotal);
                $this->em->persist($entity);
            }
            
            $this->em->flush();

            $io->progressFinish();
        

        $io->success('Importation terminée');
    }

    private function readCsvFile(): Reader
    {
        $csv = Reader::createFromPath('%kernel.root.dir%/../import/ct.csv','r');
        $csv->setHeaderOffset(0);

        return $csv;
    }

    private function createOrUpdate(array $array): TechnicalAdvisor
    {

        $technicalAdvisor = $this->technicalAdvisorRepository->findOneByLastName($array['Nom']);

        if(!$technicalAdvisor){
            $technicalAdvisor = new TechnicalAdvisor();
        }

        $manager = $this->managerRepository->findAoForCtImportation($array['Nom AO']);
        if(!$manager){
            dump('no manager for ' . $array['Nom AO']);
            $manager = new Manager();
            $manager->setFirstName('A définir')
                ->setLastName($array['Nom AO'])
                ->setManagerClass($this->managerClassRepository->findOneByName('AO'))
                ->setEmail('A définir')
                ->setPhone('A définir');
            $this->em->persist($manager);
            $this->em->flush($manager);
        }

        $attachmentShop = $this->shopRepository->findOneByCm($array['CM Admin']);
        if(!$attachmentShop){
            dd('no attachmentShop for ' . $array['CM Admin']);
        }


        //Secteur AO,Nom AO,Nom,Prénom,email,Tél fixe,Tél mobile,CM Admin,Adresse,Code Postal,Ville,Nom_RRH,Nom_GRH
        $technicalAdvisor
            ->setFirstName($array['Prénom'])
            ->setLastName($array['Nom'])
            ->setEmail($array['email'])
            ->setPhone($array['Tél mobile'])
            ->setManager($manager)
            ->setAttachmentCenter($attachmentShop)
            ->setZoneColor($this->mapService->randomHexadecimalColor())
            ;

        return $technicalAdvisor;
    }

}