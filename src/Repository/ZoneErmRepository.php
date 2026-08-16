<?php

namespace App\Repository;

use App\Entity\ZoneErm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ZoneErm>
 */
class ZoneErmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZoneErm::class);
    }

    public function findByClasse(string $classe): array
    {
        // Précharge shops/city/department/manager/roles en 1 requête pour éviter
        // le N+1 (chaque zone->getShops()->getCity()->getDepartment() déclenche
        // sinon une requête par centre lors de l'affichage de la carte).
        return $this->createQueryBuilder('z')
            ->leftJoin('z.shops', 'shops')->addSelect('shops')
            ->leftJoin('shops.city', 'city')->addSelect('city')
            ->leftJoin('city.department', 'department')->addSelect('department')
            ->leftJoin('z.manager', 'manager')->addSelect('manager')
            ->leftJoin('manager.roles', 'managerRoles')->addSelect('managerRoles')
            ->where('z.name LIKE :val')
            ->setParameter('val', '%'.$classe.'%')
            ->orderBy('z.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return ZoneErm[] Returns an array of ZoneErm objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('z.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ZoneErm
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
