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

       public function findAllTelematicTechniciansByFormationName(array $formationNames = []): ?array
       {
            if(count($formationNames) == 0){

                return $this->createQueryBuilder('t')
                    ->where('t.isTelematic = :true')
                    ->setParameter('true', true)
                    ->getQuery()
                    ->getResult()
                    ;
                    
            }else{

                return $this->createQueryBuilder('t')
                    ->join('t.technicianFormations', 'f')
                    ->where('t.isTelematic = :isTelematic')
                    ->setParameter('isTelematic', true)
                    ->andWhere('f.name IN (:formationNames)')
                    ->setParameter('formationNames', $formationNames)
                    ->groupBy('t.id') // Group by technician to count their distinct formations
                    ->having('COUNT(DISTINCT f.name) = :formationCount') // Ensure they have all specified formations
                    ->setParameter('formationCount', count($formationNames))
                    ->getQuery()
                    ->getResult();
            }
       }
}
