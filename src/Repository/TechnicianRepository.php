<?php

namespace App\Repository;

use App\Entity\Technician;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

   // src/Repository/TechnicianRepository.php

    public function findAllTelematicTechnicians(
        array $formationNames = [],
        array $functionNames = [],
        array $vehicleNames = []
    ): array {
        $qb = $this->createQueryBuilder('t');

        // Condition de base
        $qb->where('t.isTelematic = :true')
        ->setParameter('true', true);

        // Ajout des conditions pour les formations
        if (!empty($formationNames)) {
            $qb->join('t.technicianFormations', 'f')
            ->andWhere('f.name IN (:formationNames)')
            ->setParameter('formationNames', $formationNames);
        }

        // Ajout des conditions pour les fonctions
        if (!empty($functionNames)) {
            $qb->join('t.fonctions', 'ff')
            ->andWhere('ff.name IN (:functionNames)')
            ->setParameter('functionNames', $functionNames);
        }

        // Ajout de la nouvelle condition pour les véhicules
        if (!empty($vehicleNames)) {
            $qb->join('t.vehicle', 'v')
            ->andWhere('v.name IN (:vehicleNames)')
            ->setParameter('vehicleNames', $vehicleNames);
        }

        // Le tri est optionnel, mais peut être utile pour l'affichage
        $qb->orderBy('t.id', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
