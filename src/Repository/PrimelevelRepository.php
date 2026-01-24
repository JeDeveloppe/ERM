<?php

namespace App\Repository;

use App\Entity\Primelevel;
use App\Entity\Staff;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Primelevel>
 */
class PrimelevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Primelevel::class);
    }

    public function findPrimeLevelWherePsByPersonIsBetweenStartAndEnd(int $psByPerson, string $version)
    {

        // 1. On récupère d'abord tous les paliers DE CETTE VERSION uniquement
        // On trie par 'start' pour que le end() fonctionne correctement
        $primeLevels = $this->findBy(['version' => $version], ['start' => 'ASC']);
        
        if (empty($primeLevels)) {
            return null;
        }
        
        $lastLevel = end($primeLevels);
        if ($psByPerson >= $lastLevel->getEnd()) {
            return $lastLevel;
        }

        $query = $this->createQueryBuilder('p')
            ->where('p.start <= :val')
            ->andWhere('p.version = :version')
            ->andWhere('p.end > :val')
            ->setParameter('val', $psByPerson)
            ->setParameter('version', $version)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        return $query;
    }

    public function findAllVersions(): array
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.version')
            ->distinct()
            ->orderBy('p.version', 'DESC')
            ->getQuery()
            ->getResult();

        // On transforme le résultat en tableau simple [ "2026_1" => "2026_1", ... ]
        $choices = [];
        foreach ($results as $r) {
            $choices[$r['version']] = $r['version'];
        }
        return $choices;
    }

    //    /**
    //     * @return Primelevel[] Returns an array of Primelevel objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Primelevel
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
