<?php

namespace App\Command;

use App\Entity\ShopClass;
use App\Service\CgoService;
use App\Service\CityService;
use App\Service\DepartmentService;
use App\Service\LargeRegionService;
use App\Service\ManagerClassService;
use App\Service\ManagerService;
use App\Service\RegionErmService;
use App\Service\RegionErmServiceService;
use App\Service\ShopClassService;
use App\Service\ShopService;
use App\Service\TelematicAreasService;
use App\Service\UserService;
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
        private ShopService $shopService,
        private ManagerClassService $managerClassService,
        private CgoService $cgoService,
        private TelematicAreasService $telematicAreasService,
        private UserService $userService
        )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // ini_set('memory_limit', '2048M');
        ini_set("memory_limit", -1);

        $io = new SymfonyStyle($input,$output);

        $this->userService->initAdmin($io);
        $this->largeregionService->importLargesregions($io);
        $this->departmentService->importDepartementsFrancais($io);
        $this->cityService->importCitiesOfFrance($io);
        $this->managerClassService->importManagerClass($io);
        $this->regionErmService->importRegionserm($io);
        $this->zoneErmService->importZoneserm($io);
        $this->shopClassService->importShopClasses($io);
        $this->managerService->importRcsManagers($io);
        $this->managerService->importDrManagers($io);
        $this->managerService->importRAVL_RZManagers($io);
        $this->managerService->importAOManagers($io);
        $this->shopService->importShops($io);
        $this->cgoService->importCgos($io);
        $this->cgoService->importShopsUnderControls($io);
        $this->shopService->updateShops($io);
        $this->telematicAreasService->importCgoTelematicAreas($io);
        $this->telematicAreasService->importDepartmentsInTelematicsAreas($io);
        $this->cgoService->updateCgos($io);

        return Command::SUCCESS;
    }

}