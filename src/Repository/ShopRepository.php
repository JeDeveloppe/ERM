<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Department;
use App\Entity\Technician;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function findShopsByTechnicians(array $technicians): array
    {
        // Si la liste de techniciens est vide, on retourne un tableau vide pour éviter une erreur
        if (empty($technicians)) {
            return [];
        }

        $qb = $this->createQueryBuilder('s');
        
        // On fait un innerJoin sur les techniciens de la boutique
        $qb->innerJoin('s.technicians', 't');

        // On filtre sur les techniciens qui sont dans la liste fournie
        $qb->where('t.id IN (:technicianIds)')
           ->setParameter('technicianIds', array_map(fn(Technician $tech) => $tech->getId(), $technicians));

        // On regroupe par boutique pour avoir un résultat unique
        $qb->groupBy('s.id');

        return $qb->getQuery()->getResult();
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
