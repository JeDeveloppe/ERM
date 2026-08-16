<?php

namespace App\Repository;

use App\Entity\Cgo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cgo>
 */
class CgoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cgo::class);
    }

    /**
     * Précharge city/shopsUnderControls/leur ville/leurs personnes/rôles en 1
     * requête pour éviter le N+1 lors de l'affichage de la carte "centres sous CGO".
     *
     * @param mixed $classErm
     * @return Cgo[]
     */
    public function findByClassErmWithRelations($classErm): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.city', 'city')->addSelect('city')
            ->leftJoin('c.shopsUnderControls', 'shops')->addSelect('shops')
            ->leftJoin('shops.city', 'shopCity')->addSelect('shopCity')
            ->leftJoin('shops.people', 'people')->addSelect('people')
            ->leftJoin('people.roles', 'peopleRoles')->addSelect('peopleRoles')
            ->where('c.classErm = :classErm')
            ->setParameter('classErm', $classErm)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneCgoByZoneNameAndClasse(string $zoneName, string $classe)
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.zoneName = :zoneName')
            ->setParameter('zoneName', $zoneName)
            ->andWhere('c.name LIKE :classe')
            ->setParameter('classe', '%'.$classe.'%')
            ->getQuery()
            ->getOneOrNullResult();
        return $query;
    }

    //    /**
    //     * @return Cgo[] Returns an array of Cgo objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cgo
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
