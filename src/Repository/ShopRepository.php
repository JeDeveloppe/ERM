<?php

namespace App\Repository;

use App\Entity\Department;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shop>
 */
class ShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    /**
    * @return Shop[] Returns an array of Shop objects
    */
    public function findAllShopsFromDepartment(Department $department): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.city', 'city')
            ->where('city.department = :department')
            ->setParameter('department', $department)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Undocumented function
     *
     * @param array $classErm
     * @return array
     */
    public function findShopsWhereTechnicianIsTelematic(): array
    {

        $qb = $this->createQueryBuilder('s')
            ->join('s.technicians', 't')
            ->where('t.isTelematic = :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getResult()
        ;

        return array_unique($qb);
    }

    public function findShopsforDepannage(array $classErm): array
    {
        $qp = $this->createQueryBuilder('s')
            ->join('s.shopClass', 'c')
            ->where('c.name IN (:classErm)')
            ->setParameter('classErm', $classErm)
            ->getQuery()
            ->getResult()
        ;

        return array_unique($qp);
    }

    //    /**
    //     * @return Shop[] Returns an array of Shop objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Shop
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
