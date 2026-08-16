<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\RoleErm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @return Person[]
     */
    public function findByRole(string $roleName): array
    {
        // Précharge shop/workForShops (+ leur ville/personnes/rôles) en 1 requête
        // pour éviter le N+1 lors de l'affichage de la carte des CT.
        return $this->createQueryBuilder('p')
            ->join('p.roles', 'r')
            ->leftJoin('p.shop', 'shop')->addSelect('shop')
            ->leftJoin('shop.city', 'shopCity')->addSelect('shopCity')
            ->leftJoin('p.workForShops', 'workForShops')->addSelect('workForShops')
            ->leftJoin('workForShops.city', 'workForShopsCity')->addSelect('workForShopsCity')
            ->leftJoin('workForShops.people', 'workForShopsPeople')->addSelect('workForShopsPeople')
            ->leftJoin('workForShopsPeople.roles', 'workForShopsPeopleRoles')->addSelect('workForShopsPeopleRoles')
            ->where('r.name = :roleName')
            ->setParameter('roleName', $roleName)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function findByAnyRole(array $roleNames): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.roles', 'r')
            ->where('r.name IN (:roleNames)')
            ->setParameter('roleNames', $roleNames)
            ->getQuery()
            ->getResult();
    }

    public function findAoForCtImportation(string $name): ?Person
    {
        // setMaxResults(1) plutôt que getOneOrNullResult() sans limite : plusieurs AO
        // peuvent partager le même nom de famille, on prend le premier trouvé plutôt
        // que de planter l'import sur une ambiguïté.
        return $this->createQueryBuilder('p')
            ->join('p.roles', 'r')
            ->where('p.name = :name')
            ->andWhere('r.name = :roleName')
            ->setParameter('name', $name)
            ->setParameter('roleName', RoleErm::AO)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllTelematicPeople(
        array $formationNames = [],
        array $functionNames = [],
        array $vehicleNames = []
    ): array {
        $qb = $this->createQueryBuilder('t');

        // On s'assure que la personne a le rôle télématique
        $qb->join('t.roles', 'role')
            ->where('role.name = :roleName')
            ->setParameter('roleName', RoleErm::TECHNICIEN_TELEMATIQUE);
        // Si des formations sont fournies, on ajoute une jointure et des conditions
        if (!empty($formationNames)) {
            // LEFT JOIN pour ne pas exclure les techniciens sans formations
            $qb->leftJoin('t.technicianFormations', 'tf');

            // On filtre sur les formations spécifiques
            $qb->andWhere('tf.name IN (:formationNames)')
                ->setParameter('formationNames', $formationNames);

            // Et on s'assure qu'ils ont EXACTEMENT ces formations
            // On ajoute un GROUP BY sur le technicien pour pouvoir compter
            $qb->groupBy('t.id');

            // On utilise HAVING pour s'assurer que le nombre de formations trouvées
            // est égal au nombre de formations recherchées
            $qb->having('COUNT(tf.id) = :formationCount')
                ->setParameter('formationCount', count($formationNames));
        }

        // Ajout des conditions pour les fonctions (la logique actuelle est correcte)
        if (!empty($functionNames)) {
            $qb->join('t.fonctions', 'ff')
            ->andWhere('ff.name IN (:functionNames)')
            ->setParameter('functionNames', $functionNames);
        }

        // Ajout des conditions pour les véhicules (la logique actuelle est correcte)
        if (!empty($vehicleNames)) {
            $qb->join('t.vehicle', 'v')
            ->andWhere('v.name IN (:vehicleNames)')
            ->setParameter('vehicleNames', $vehicleNames);
        }

        // Le tri est conservé
        $qb->orderBy('t.id', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
