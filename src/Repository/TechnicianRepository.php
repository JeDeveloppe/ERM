<?php

namespace App\Repository;

use App\Entity\Technician;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Technician>
 */
class TechnicianRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technician::class);
    }

    //    /**
    //     * @return Technician[] Returns an array of Technician objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

       public function findAllTelematicTechniciansByFormationName($formationName): ?array
       {
           return $this->createQueryBuilder('t')
               ->join('t.technicianFormations', 'f')
               ->where('t.isTelematic = :true')
               ->andWhere('f.name IN (:formationName)')
               ->setParameter('true', true)
               ->setParameter('formationName', $formationName)
               ->getQuery()
               ->getResult()
           ;
       }
}
