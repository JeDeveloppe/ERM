<?php

namespace App\Service;

use App\Entity\TechnicianFonction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\TechnicianFonctionRepository;

class TechnicianFonctionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TechnicianFonctionRepository $technicianFonctionRepository,
        private Security $security
        ){
    }

    public function initDatabase($io): void
    {
        $io->title('Création / mise à jour des fonctions en BDD');

        $vehicles = [
            "CENTRE TM VL ACTIF",
            "REFERENT",
            "SPECIALISTE",
            "Formation (REFERENT)"
        ];

        foreach($vehicles as $toCreate) {
            //on vérifié si on a déjà créé le vehicule
            $vehicle = $this->technicianFonctionRepository->findOneBy(['name' => $toCreate]);

            if(!$vehicle){
                $vehicle = new TechnicianFonction();
            }

            $vehicle->setName($toCreate)->setColor('#'.dechex(mt_rand(0, 0xFFFFFF)));

            $this->em->persist($vehicle);
        }

        $this->em->flush();

        $io->success('Véhicules créés / mis à jour!');

    }

}