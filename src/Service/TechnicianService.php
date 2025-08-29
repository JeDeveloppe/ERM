<?php

namespace App\Service;

use League\Csv\Reader;
use App\Entity\Technician;
use App\Repository\CgoRepository;
use App\Repository\TechnicianRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ShopRepository;
use App\Repository\TechnicianFonctionRepository;
use App\Repository\TechnicianFormationsRepository;
use App\Repository\TechnicianVehicleRepository;
use Symfony\Component\Console\Style\SymfonyStyle;

class TechnicianService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopRepository $shoprepository,
        private TechnicianRepository $technicianRepository,
        private TechnicianFormationsRepository $technicianFormationsRepository,
        private TechnicianVehicleRepository $technicianVehicleRepository,
        private CgoRepository $cgoRepository,
        private TechnicianFonctionRepository $technicianFonctionRepository
        ){
    }

    public function importTechnicians(SymfonyStyle $io): void
    {
        $io->title('Importation des techniciens');

        $technicians = $this->readCsvFileTechnicians();
        
        $io->progressStart(count($technicians));

        foreach($technicians as $array){
            $io->progressAdvance();
            
            try {
                // Appelle la méthode pour créer ou mettre à jour le technicien
                $technician = $this->createOrUpdateTechnician($array, $io);

                // Si la méthode retourne un objet Technician, on le persiste
                if ($technician) {
                    $this->em->persist($technician);
                    // On peut opter pour un flush hors de la boucle pour de meilleures performances
                    // Mais pour cet exemple, on le garde pour une visibilité immédiate
                    $this->em->flush();
                } else {
                    // Log un message d'information si le technicien est ignoré
                    $io->info(sprintf('Technicien ignoré. Email : %s', $array['Email TM']));
                }
            } catch (\Exception $e) {
                // Log l'erreur si une exception survient pendant la persistance
                $io->error(sprintf('Error importing technician with email "%s": %s', $array['Email TM'], $e->getMessage()));
                continue; // Continue la boucle pour les autres techniciens
            }
        }

        $io->progressFinish();
        $io->success('Importation des techniciens terminée');
    }

    //lecture des fichiers exportes dans le dossier import
    private function readCsvFileTechnicians(): Reader
    {
        $csvtechnicians = Reader::createFromPath('%kernel.root.dir%/../import/technicians.csv','r');
        $csvtechnicians->setHeaderOffset(0);

        return $csvtechnicians;
    }

    private function createOrUpdateTechnician(array $arrayTechnician, SymfonyStyle $io): ?Technician
    {
        $shopInBdd = $this->shoprepository->findOneBy(['cm' => $arrayTechnician['CM']]);
        $technician = $this->technicianRepository->findOneBy(['email' => $arrayTechnician['Email TM'], 'shop' => $shopInBdd]);
        $defaultCgo = $this->cgoRepository->findOneBy(['cm' => 3429]);

        if(!$technician){
            $io->warning(sprintf('Skipping technician. Technician with email "%s" not found.', $arrayTechnician['Email TM']));
            $technician = new Technician($defaultCgo);
        }

        //?on recupere le shop du technicien
        $shop = $this->shoprepository->findOneBy(['cm' => $arrayTechnician['CM']]);
        if(!$shop){
            // On log l'erreur et on retourne null au lieu de stopper le script
            $io->warning(sprintf('Skipping technician. Shop with CM "%s" not found for technician with email "%s".', $arrayTechnician['CM'], $arrayTechnician['Email TM']));
            return null;
        }

        //?on recupere le cgo ratacher au shop (peut etre null)
        $cgos = $shop->getCgos();

        if($cgos == null || count($cgos) == 0){
            $cgo = $defaultCgo;
        }else{
            $cgo = $cgos[0];
        }

        $nameAndFirstName = explode(" ", $arrayTechnician['Télématique VI TM1']);
        $technicianName = $nameAndFirstName[0] ?? ''; // Utiliser l'opérateur de coalescence nulle pour éviter les erreurs
        $technicianFirstName = $nameAndFirstName[1] ?? ''; // Si l'index 1 n'existe pas, la valeur sera une chaîne vide

        // Si vous souhaitez gérer le cas où il n'y a qu'un seul mot (probablement le nom de famille), vous pouvez faire ceci :
        if (count($nameAndFirstName) > 1) {
            $technicianName = $nameAndFirstName[0];
            $technicianFirstName = $nameAndFirstName[1];
        } else {
            $technicianName = $nameAndFirstName[0] ?? '';
            $technicianFirstName = ''; // Pas de prénom trouvé
        }

        $fonction = $this->technicianFonctionRepository->findOneBy(['name' => $arrayTechnician['Fonctions']]);
        if(!$fonction){
            // On log l'erreur et on retourne null au lieu de stopper le script
            $io->warning(sprintf('Skipping technician. Fonction "%s" not found for technician with email "%s".', $arrayTechnician['Fonctions'], $arrayTechnician['Email TM']));
            return null;
        }

        $technician
            ->setEmail($arrayTechnician['Email TM'] ?? $shop->getEmail())
            ->setName($technicianName)
            ->setIsTelematic(true)
            ->setControledByCgo($cgo)
            ->addFonction($fonction)
            ->setIsTelematic(true)
            ->setFirstName($technicianFirstName)
            ->setPhone($arrayTechnician['Téléphone Portable'] ?? $shop->getPhone())
            ->setInformations($arrayTechnician['INFOS DIVERS'])
            ->setShop($shop)
            ->setVehicle($this->technicianVehicleRepository->findOneBy(['name' => $arrayTechnician['Véhicule']]) ?? $this->technicianVehicleRepository->findOneBy(['name' => 'SANS VEHICULE']))
            ;

        // Vider la collection de formations avant d'ajouter les nouvelles
        foreach ($technician->getTechnicianFormations() as $formation) {
            $technician->removeTechnicianFormation($formation);
        }
        
        $formations = $this->technicianFormationsRepository->findAll();

        foreach($formations as $formation){
            $this->testFormationIsOkorKo($formation->getName(), $arrayTechnician[$formation->getName()], $technician);
        }

        return $technician;

    }

    private function testFormationIsOkorKo(string $formationName, ?string $okOrKo = 'INDEFINIE', Technician $technician)
    {
        $formationEntity = $this->technicianFormationsRepository->findOneBy(['name' => $formationName]);
        $m400vl = $this->technicianFormationsRepository->findOneBy(['name' => 'M400+HMI+CAN VL']);

        $technician->addTechnicianFormation($m400vl); //?ajout de la formation m400+HMI+CAN VL par default à tout le monde

        if($formationEntity){
            if($okOrKo === 'OK' or $okOrKo === 'oui' or $okOrKo === 'OUI'){
                $technician->addTechnicianFormation($formationEntity);
            }
        }else{
            echo 'KO n existe pas ' . $formationName;
        }
    }
}
