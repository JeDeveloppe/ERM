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
     * Tables de jointure (relations many-to-many) vidées par --reset. Elles ne
     * se dupliquent jamais (les entités ajoutent via des méthodes qui vérifient
     * `contains()` avant d'ajouter), mais les vider permet de refléter les
     * suppressions de relations dans le CSV source (une personne qui ne
     * travaille plus pour tel centre, etc.) — un import normal n'ajoute que
     * des relations, il n'en retire jamais.
     *
     * Ne touche PAS aux tables "identité" (person, cgo, shop, zone_erm,
     * region_erm, telematic_area, technician_vehicle/fonction/formations,
     * role_erm...) : leurs services dédoublonnent déjà par nom/CM avant
     * création (findOneBy), donc les vider ne sert à rien pour éviter les
     * doublons, et ça casse en plus les ID stables dont dépendent les
     * relations montées à la main dans l'admin (ex: département → région
     * ERM, couleurs personnalisées).
     */
    private const TABLES_TO_RESET = [
        'person_role_erm',
        'person_technician_fonction',
        'person_technician_formations',
        'person_work_for_shop',
        'cgo_shop',
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
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Vide les tables de jointure (relations many-to-many) avant de réimporter, pour refléter les relations supprimées côté CSV. Ne touche pas aux entités elles-mêmes (déjà dédoublonnées par nom/CM à l\'import).');
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
        $this->departmentService->importDepartmentRegionErm($io);
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
        $io->title('Remise à zéro des tables de jointure avant réimport');

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach (self::TABLES_TO_RESET as $table) {
            $this->connection->executeStatement("TRUNCATE `$table`");
            $io->text("Table vidée : $table");
        }

        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $io->success('Tables de jointure vidées, réimport en cours...');
    }

}
