<?php

namespace App\Repository;

use App\Entity\Shop;
use App\Entity\Department;
use App\Entity\Person;
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
     * Charge tous les centres avec leur ville et les personnes rattachées (+ leurs
     * rôles) en une seule requête, pour éviter le N+1 (getCity()/getRcsPerson()
     * déclenchant sinon une requête par centre lors de l'affichage des cartes).
     *
     * @return Shop[]
     */
    public function findAllWithCityAndPeopleRoles(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.city', 'city')->addSelect('city')
            ->leftJoin('s.people', 'people')->addSelect('people')
            ->leftJoin('people.roles', 'roles')->addSelect('roles')
            ->getQuery()
            ->getResult()
        ;
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

    public function findShopsByPeople(array $people): array
    {
        // Si la liste de techniciens est vide, on retourne un tableau vide pour éviter une erreur
        if (empty($people)) {
            return [];
        }

        $qb = $this->createQueryBuilder('s');
        
        // On fait un innerJoin sur les techniciens de la boutique
        $qb->innerJoin('s.people', 't');

        // On filtre sur les techniciens qui sont dans la liste fournie
        $qb->where('t.id IN (:personIds)')
           ->setParameter('personIds', array_map(fn(Person $tech) => $tech->getId(), $people));

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
