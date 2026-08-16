<?php

namespace App\Service;

use App\Entity\RoleErm;
use App\Repository\RoleErmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RoleErmService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RoleErmRepository $roleErmRepository,
        ){
    }

    public function seedRoles(SymfonyStyle $io): void
    {
        $io->title('Création des rôles ERM');

            $io->progressStart(count(RoleErm::ALL));

            foreach (RoleErm::ALL as $name) {

                $io->progressAdvance();

                $role = $this->roleErmRepository->findOneBy(['name' => $name]);

                if (!$role) {
                    $role = new RoleErm();
                    $role->setName($name);
                    $this->em->persist($role);
                }
            }

            $this->em->flush();

            $io->progressFinish();

        $io->success('Rôles créés');
    }
}
