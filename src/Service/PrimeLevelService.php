<?php

namespace App\Service;

use App\Entity\Primelevel;
use App\Entity\RegionErm;
use App\Entity\ShopClass;
use App\Entity\Staff;
use App\Entity\ZoneErm;
use App\Repository\PrimelevelRepository;
use App\Repository\RegionErmRepository;
use App\Repository\ShopClassRepository;
use League\Csv\Reader;
use App\Repository\ZoneErmRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

class PrimeLevelService
{
    public function __construct(
        private PrimelevelRepository $primelevelRepository,
    ) {}

    public function getPrimeLevel(int $psByPerson, string $version): Primelevel|null
    {

        $primeLevelresult = $this->primelevelRepository->findPrimeLevelWherePsByPersonIsBetweenStartAndEnd($psByPerson, $version);

        return $primeLevelresult;
    }

    public function getPsByPerson(int $fullPs, float $divider): int
    {
        return $fullPs / $divider;
    }

    public function calculateValuePrimeByPerson(float $psByPersonZone, Primelevel $primeLevel, int $max): float
    {

        $calculatedValue = $primeLevel->getPercentage() / 100 * $psByPersonZone;

        if ($calculatedValue > $max) {
            $calculatedValue = $max;
        }

        return $calculatedValue;
    }

    public function returnInfosForNextLevel(float $divider, int $fullPs, Primelevel $nextLevel, int $max): array
    {
        $start = $nextLevel->getStart();
        $nextPsForNextLevel = $start * $divider;
        $psDifference = $nextPsForNextLevel - $fullPs;
        $startPrime = $this->calculateValuePrimeByPerson($nextLevel->getStart(), $nextLevel, $max);
        $endPrime = $this->calculateValuePrimeByPerson($nextLevel->getEnd(), $nextLevel, $max);

        //? If the endPrime is greater than 600, we set it to 600
        if ($endPrime > 600) {
            $endPrime = 600;
        }


        return [
            'nextPsForNextLevel' => $nextPsForNextLevel,
            'psDifference' => $psDifference,
            'startPrime' => $startPrime,
            'endPrime' => $endPrime
        ];
    }

    public function comparePrimeVersions(int $psByPerson, string $oldVersion, string $newVersion): array
    {
        // On va chercher le palier pour l'ancienne version
        $oldLevel = $this->primelevelRepository->findPrimeLevelWherePsByPersonIsBetweenStartAndEnd($psByPerson, $oldVersion);

        // On va chercher le palier pour la nouvelle version
        $newLevel = $this->primelevelRepository->findPrimeLevelWherePsByPersonIsBetweenStartAndEnd($psByPerson, $newVersion);

        return [
            'old' => [
                'version' => $oldVersion,
                'percentage' => $oldLevel ? $oldLevel->getPercentage() : 0,
                'value' => $oldLevel ? $this->calculateValuePrimeByPerson($psByPerson, $oldLevel, 600) : 0
            ],
            'new' => [
                'version' => $newVersion,
                'percentage' => $newLevel ? $newLevel->getPercentage() : 0,
                'value' => $newLevel ? $this->calculateValuePrimeByPerson($psByPerson, $newLevel, 600) : 0
            ],
            'diff' => ($newLevel ? $newLevel->getPercentage() : 0) - ($oldLevel ? $oldLevel->getPercentage() : 0)
        ];
    }
}
