<?php

namespace App\Controller\Admin;

use App\Entity\Cgo;
use App\Entity\City;
use App\Entity\Shop;
use App\Entity\Manager;
use App\Entity\ZoneErm;
use App\Entity\RegionErm;
use App\Entity\ShopClass;
use App\Entity\Department;
use App\Entity\LargeRegion;
use App\Entity\ManagerClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        return $this->render('admin/dashboard.html.twig', []);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('EuromasterMaps');
    }

    public function configureMenuItems(): iterable
    {
        return [

            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToRoute('Site', 'fa fa-globe', 'app_home'),
            MenuItem::section('Zones ERM:'),
            // MenuItem::linkToCrud('Zones opérationnelles (par centres)', 'fas fa-list', CgoOperationalAreaByShops::class),
            // MenuItem::linkToCrud('Zones télématiques', 'fas fa-list', CgoTelematicArea::class),
            MenuItem::section('Configurations ERM:'),
            MenuItem::linkToCrud('Les managers', 'fas fa-list', Manager::class),
            MenuItem::linkToCrud('Les cgos', 'fas fa-list', Cgo::class),
            MenuItem::linkToCrud('Les status des managers', 'fas fa-list', ManagerClass::class),
            MenuItem::linkToCrud('Les centres', 'fas fa-list', Shop::class),
            MenuItem::linkToCrud('Les classes', 'fas fa-list', ShopClass::class),
            MenuItem::linkToCrud('Les régions ERM', 'fas fa-list', RegionErm::class),
            MenuItem::linkToCrud('Les zones ERM', 'fas fa-list', ZoneErm::class),
            MenuItem::section('Configurations FRANCE:'),
            MenuItem::linkToCrud('Les villes', 'fas fa-list', City::class),
            MenuItem::linkToCrud('Départements', 'fas fa-list', Department::class),
            MenuItem::linkToCrud('Les grandes régions', 'fas fa-list', LargeRegion::class),
        ];

    }
}
