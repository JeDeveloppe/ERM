<?php

namespace App\Command;

use App\Entity\TechnicalAdvisor;
use App\Repository\DepartmentRepository;
use App\Service\DepartmentService;
use App\Service\TechnicalAdvisorService;
use App\Service\TechnicianFonctionService;
use App\Service\TechnicianFormationsService;
use App\Service\TechnicianService;
use App\Service\TechnicianVehicleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:initdatabase2')]

class InitDataBase2 extends Command
{
    public function __construct(
        private TechnicianService $technicianService,
        private TechnicianFormationsService $technicianFormationsService,
        private TechnicianVehicleService $technicianVehicleService,
        private TechnicalAdvisorService $technicalAdvisorService,
        private DepartmentService $departmentService,
        private TechnicianFonctionService $technicianFonctionService
        )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // ini_set('memory_limit', '2048M');
        ini_set("memory_limit", -1);

        $io = new SymfonyStyle($input,$output);

        $this->technicianFonctionService->initDatabase($io);
        $this->technicianVehicleService->initDatabase($io);
        $this->technicianFormationsService->initDatabase($io);
        $this->technicianService->importTechnicians($io);
        $this->technicalAdvisorService->importCTs($io);
        $this->departmentService->importDepartmentsWithGpsPoints($io);


        return Command::SUCCESS;
    }

}