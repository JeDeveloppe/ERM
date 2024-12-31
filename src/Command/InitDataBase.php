<?php

namespace App\Command;

use App\Entity\ShopClass;
use App\Service\CityService;
use App\Service\DepartmentService;
use App\Service\LargeRegionService;
use App\Service\ManagerService;
use App\Service\RegionErmService;
use App\Service\RegionErmServiceService;
use App\Service\ShopClassService;
use App\Service\ShopService;
use App\Service\ZoneErmService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:initdatabase')]

class InitDataBase extends Command
{
    public function __construct(
        private LargeRegionService $largeregionService,
        private DepartmentService $departmentService,
        private CityService $cityService,
        private RegionErmService $regionErmService,
        private ZoneErmService $zoneErmService,
        private ShopClassService $shopClassService,
        private ManagerService $managerService,
        private ShopService $shopService
        )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // ini_set('memory_limit', '2048M');
        ini_set("memory_limit", -1);

        $io = new SymfonyStyle($input,$output);

        // $this->largeregionService->importLargesregions($io);
        // $this->departmentService->importDepartementsFrancais($io);
        // $this->cityService->importCitiesOfFrance($io);
        // $this->regionErmService->importRegionserm($io);
        // $this->zoneErmService->importZoneserm($io);
        // $this->shopClassService->importShopClasses($io);
        // $this->managerService->importManagers($io);
        $this->shopService->importShops($io);

        return Command::SUCCESS;
    }

}