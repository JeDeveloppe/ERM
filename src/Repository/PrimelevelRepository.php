<?php

namespace App\Repository;

use App\Entity\Primelevel;
use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Primelevel>
 */
class PrimelevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Primelevel::class);
    }

    public function findPrimeLevelWhereStartIsBigerAndEndIsLowerByStaff(int $psByPerson)
    {

        $query = $this->createQueryBuilder('p')
            ->where('p.start <= :val')
            ->andWhere('p.end > :val')
            ->setParameter('val', $psByPerson)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        return $query;
    }


    //    /**
    //     * @return Primelevel[] Returns an array of Primelevel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Primelevel
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
