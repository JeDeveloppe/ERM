<?php

namespace App\Repository;

use App\Entity\Collaborator;
use App\Entity\CollaboratorAbsence;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Absence>
 */
class CollaboratorAbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollaboratorAbsence::class);
    }

    public function findAbsencesForCurrentYear($user): array
    {
        $year = (new \DateTime())->format('Y');
        $startOfYear = new \DateTime($year . '-01-01 00:00:00');
        $endOfYear = new \DateTime($year . '-12-31 23:59:59');

        return $this->createQueryBuilder('a')
            ->where('a.owner = :user')
            ->andWhere('a.startDate >= :start')
            ->andWhere('a.startDate <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfYear)
            ->setParameter('end', $endOfYear)
            ->orderBy('a.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les absences qui chevauchent le mois sélectionné
     */
    public function findAbsencesByMonth(\App\Entity\User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.owner = :user')
            // On récupère les absences qui commencent avant la fin du mois
            // ET qui finissent après le début du mois
            ->andWhere('a.startDate <= :endDate')
            ->andWhere('a.endDate >= :startDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
