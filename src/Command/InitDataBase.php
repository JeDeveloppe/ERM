<?php

namespace App\Command;

use App\Entity\ShopClass;
use App\Service\CgoService;
use App\Service\CityService;
use App\Service\DepartmentService;
use App\Service\LargeRegionService;
use App\Service\RoleErmService;
use App\Service\PersonService;
use App\Service\RegionErmService;
use App\Service\ShopClassService;
use App\Service\ShopService;
use App\Service\TelematicAreasService;
use App\Service\UserService;
use App\Service\ZoneErmService;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:initdatabase')]

class InitDataBase extends Command
{
    /**
     * Tables (re)créées par app:initdatabase / app:initdatabase2, dans un ordre
     * où les tables "enfants" (jointures) sont vidées avant les tables "parents".
     * Ne touche pas aux tables de référence (city, department, shop_class,
     * large_region, role_erm...) qui sont peuplées par upsert (findOneBy avant
     * création) et ne se dupliquent donc jamais — et pour role_erm, ça évite
     * aussi de perdre les couleurs configurées à la main dans l'admin.
     */
    private const TABLES_TO_RESET = [
        'person_role_erm',
        'person_technician_fonction',
        'person_technician_formations',
        'person_work_for_shop',
        'cgo_shop',
        'person',
        'cgo',
        'shop',
        'zone_erm',
        'region_erm',
        'telematic_area',
        'technician_vehicle',
        'technician_fonction',
        'technician_formations',
    ];

    public function __construct(
        private LargeRegionService $largeregionService,
        private DepartmentService $departmentService,
        private CityService $cityService,
        private RegionErmService $regionErmService,
        private ZoneErmService $zoneErmService,
        private ShopClassService $shopClassService,
        private PersonService $personService,
        private ShopService $shopService,
        private RoleErmService $roleErmService,
        private CgoService $cgoService,
        private TelematicAreasService $telematicAreasService,
        private UserService $userService,
        private Connection $connection
        )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Vide les tables ERM avant de réimporter, pour repartir d\'une base propre (évite les doublons laissés par d\'anciens imports)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // ini_set('memory_limit', '2048M');
        ini_set("memory_limit", -1);

        $io = new SymfonyStyle($input,$output);

        if ($input->getOption('reset')) {
            $this->resetTables($io);
        }

        $this->userService->initAdmin($io);
        $this->largeregionService->importLargesregions($io);
        $this->departmentService->importDepartementsFrancais($io);
        $this->cityService->importCitiesOfFrance($io);
        $this->roleErmService->seedRoles($io);
        $this->regionErmService->importRegionserm($io);
        $this->zoneErmService->importZoneserm($io);
        $this->shopClassService->importShopClasses($io);
        $this->personService->importDR($io);
        $this->personService->importRAVL_RZ($io);
        $this->personService->importAO($io);
        $this->shopService->importShops($io);
        $this->cgoService->importCgos($io);
        $this->cgoService->importShopsUnderControls($io);
        $this->telematicAreasService->importCgoTelematicAreas($io);
        $this->telematicAreasService->importDepartmentsInTelematicsAreas($io);

        return Command::SUCCESS;
    }

    private function resetTables(SymfonyStyle $io): void
    {
        $io->title('Remise à zéro des tables ERM avant réimport');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach (self::TABLES_TO_RESET as $table) {
            $this->connection->executeStatement("TRUNCATE `$table`");
            $io->text("Table vidée : $table");
        }

        // department.region_erm_id / telematic_area_id pointeraient sinon vers
        // des lignes qui viennent d'être supprimées (region_erm/telematic_area
        // ne sont pas des tables de référence, elles sont recréées à l'import).
        $this->connection->executeStatement('UPDATE department SET region_erm_id = NULL, telematic_area_id = NULL');
        $io->text('Références region_erm_id/telematic_area_id de department réinitialisées');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $io->success('Tables ERM vidées, réimport en cours...');
    }

}
