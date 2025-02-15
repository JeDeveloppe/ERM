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
        ){
    }

    public function getPrimeLevel(int $psByPerson): Primelevel|null
    {

        $primeLevelresult = $this->primelevelRepository->findPrimeLevelWherePsByPersonIsBetweenStartAndEnd($psByPerson);

        return $primeLevelresult;

    }

    public function getPsByPerson(int $fullPs, int $divider): int
    {
        return $fullPs / $divider;
    }

    public function getValuePrimeByPerson(int $psByPerson, Primelevel $primeLevel): float
    {
        return $primeLevel->getPercentage() / 100 * $psByPerson;
    }

    public function returnInfosForNextLevel(int $divider, int $fullPs, Primelevel $nextLevel): array
    {
        $start = $nextLevel->getStart();
        $nextPsForNextLevel = $start * $divider;
        $psDifference = $nextPsForNextLevel - $fullPs;
        $startPrime = $this->getValuePrimeByPerson($nextLevel->getStart(), $nextLevel);
        $endPrime = $this->getValuePrimeByPerson($nextLevel->getEnd(), $nextLevel);

        //? If the endPrime is greater than 600, we set it to 600
        if($endPrime > 600){
            $endPrime = 600;
        }


        return [
            'nextPsForNextLevel' => $nextPsForNextLevel,
            'psDifference' => $psDifference,
            'startPrime' => $startPrime,
            'endPrime' => $endPrime
        ];
    }
}