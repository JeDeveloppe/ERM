<?php

namespace App\Repository;

use App\Entity\TelematicArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelematicArea>
 */
class TelematicAreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelematicArea::class);
    }

    
    /**
     * Précharge departments/cgos/cgo.city/cgo.manager/cgo.manager.roles en 1
     * requête pour éviter le N+1 lors de l'affichage de la carte des zones
     * télématiques.
     *
     * @return TelematicArea[]
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.departments', 'departments')->addSelect('departments')
            ->leftJoin('t.cgos', 'cgos')->addSelect('cgos')
            ->leftJoin('cgos.city', 'cgoCity')->addSelect('cgoCity')
            ->leftJoin('cgos.manager', 'manager')->addSelect('manager')
            ->leftJoin('manager.roles', 'managerRoles')->addSelect('managerRoles')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return TelematicArea[] Returns an array of TelematicArea objects
    */
    public function findTerritoryFromCgo($cgo): ?TelematicArea
    {
        return $this->createQueryBuilder('t')
            ->join('t.cgos', 'c')
            ->where('c.id = :val')
            ->setParameter('val', $cgo->getId())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    //    /**
    //     * @return TelematicArea[] Returns an array of TelematicArea objects
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

    //    public function findOneBySomeField($value): ?TelematicArea
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
