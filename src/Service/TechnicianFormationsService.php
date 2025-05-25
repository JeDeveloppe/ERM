<?php

namespace App\Service;

use App\Entity\TechnicianFormations;
use App\Entity\TechnicianVehicle;
use App\Repository\TechnicianFormationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\TechnicianVehicleRepository;

class TechnicianFormationsService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TechnicianFormationsRepository $technicianFormationsRepository
        ){
    }

    public function initDatabase($io): void
    {
        $io->title('Création / mise à jour des formations télématique en BDD');

        $formations = [
            "M400+HMI+CAN+CONNECT PL",
            "M400+HMI+CAN VL",
            "TCU-ISF / SASCAR",
            "TPMS",
            "FROID",
            "PTO",
            "ADR",
            "TACHOFRESH",
            "M120",
            "BUS",
            "HYBRIDE ELECTRIQUE",
            "DEBRIEF TELEMATIQUE"
        ];

        foreach($formations as $toCreate) {
            //on vérifié si on a déjà créé le vehicule
            $formation = $this->technicianFormationsRepository->findOneBy(['name' => $toCreate]);

            if(!$formation){
                $formation = new TechnicianFormations();
            }

            $formation->setName($toCreate)->setColor('#'.dechex(mt_rand(0, 0xFFFFFF)));

            $this->em->persist($formation);
        }

        $this->em->flush();

        $io->success('Formations créés / mis à jour!');

    }

}