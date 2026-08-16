<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\ShopClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findCitiesFromDepartements(array $departements)
    {

        return $this->createQueryBuilder('c')
            ->where('c.department IN (:departements)')
            ->setParameter('departements', $departements)
            ->orderBy('c.name', 'ASC')
        ;
    }

    /**
     * Retrouve une ville à partir d'un code postal et d'un nom approché (les CSV source
     * utilisent des noms très abrégés : "ST EGREVE", "SALAISE S SANNE", "VILLEFRANCHE
     * S/SAONE"...) - exact match d'abord, puis meilleur score de mots en commun (avec
     * "ST"→"SAINT", "STE"→"SAINTE", "S"→"SUR"), et en tout dernier recours n'importe
     * quelle ville du même département (2 premiers chiffres du code postal) pour éviter
     * un centre/CGO sans ville du tout (le code postal est parfois un code CEDEX qui ne
     * correspond à aucune commune officielle).
     */
    public function findBestMatch(string $postalCode, string $roughName): ?City
    {
        $exact = $this->findOneBy(['postalCode' => $postalCode, 'name' => $roughName]);
        if ($exact) {
            return $exact;
        }

        $candidates = $this->findBy(['postalCode' => $postalCode]);
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        if (count($candidates) > 1) {
            $roughWords = $this->normalizeToWords($roughName);
            $best = null;
            $bestScore = 0;
            foreach ($candidates as $candidate) {
                $shared = count(array_intersect($roughWords, $this->normalizeToWords($candidate->getName())));
                if ($shared > $bestScore) {
                    $bestScore = $shared;
                    $best = $candidate;
                }
            }
            if ($best !== null) {
                return $best;
            }
        }

        $deptPrefix = substr($postalCode, 0, 2);
        if ($deptPrefix === '') {
            return null;
        }

        return $this->createQueryBuilder('c')
            ->where('c.postalCode LIKE :prefix')
            ->setParameter('prefix', $deptPrefix . '%')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return string[]
     */
    private function normalizeToWords(string $value): array
    {
        $value = str_replace(['-', '/', "'"], ' ', $value);
        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        if ($transliterator !== null) {
            $value = $transliterator->transliterate($value);
        }
        $value = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $value) ?? $value;
        $words = preg_split('/\s+/', mb_strtoupper(trim($value)), -1, PREG_SPLIT_NO_EMPTY);

        $expand = ['ST' => 'SAINT', 'STE' => 'SAINTE', 'S' => 'SUR'];

        return array_map(fn($word) => $expand[$word] ?? $word, $words);
    }

    //    /**
    //     * @return City[] Returns an array of City objects
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

    //    public function findOneBySomeField($value): ?City
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
