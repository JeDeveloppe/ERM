<?php

namespace App\Service;


use App\Entity\TechnicianVehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\TechnicianVehicleRepository;

class TechnicianVehicleService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TechnicianVehicleRepository $technicianVehicleRepository,
        private Security $security
        ){
    }

    public function initDatabase($io): void
    {
        $io->title('Création / mise à jour des véhicules en BDD');

        $vehicles = [
            "VEH TELEMATIQUE (sans DMP)",
            "SANS VEHICULE",
            "CAMION ATELIER (avec DMP)"
        ];

        foreach($vehicles as $toCreate) {
            //on vérifié si on a déjà créé le vehicule
            $vehicle = $this->technicianVehicleRepository->findOneBy(['name' => $toCreate]);

            if(!$vehicle){
                $vehicle = new TechnicianVehicle();
            }

            $vehicle->setName($toCreate);

            $this->em->persist($vehicle);
        }

        $this->em->flush();

        $io->success('Véhicules créés / mis à jour!');

    }

}