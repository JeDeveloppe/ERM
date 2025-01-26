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

    public function calculPrimeByPersonByStaff(int $divider, int $fullPs): int
    {

        $psByPerson = $fullPs / $divider;

        $primeLevelresult = $this->primelevelRepository->findPrimeLevelWhereStartIsBigerAndEndIsLowerByStaff($psByPerson);
        $result = $primeLevelresult->getPercentage() / 100 * $psByPerson;

        return $result;

    }
}