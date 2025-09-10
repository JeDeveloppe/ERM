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

        // On s'assure que le technicien est télématique
        $qb->where('t.isTelematic = :isTelematic')
            ->setParameter('isTelematic', true);
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
