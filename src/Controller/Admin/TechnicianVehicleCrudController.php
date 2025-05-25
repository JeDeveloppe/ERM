<?php

namespace App\Controller\Admin;

use App\Entity\TechnicianVehicle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TechnicianVehicleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicianVehicle::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des véhicules')
            ->setPageTitle('new', 'Nouveau véhicule')
            ->setPageTitle('edit', 'Modifier un véhicule')
            ->setDefaultSort(['name' => 'ASC']);
    }
}
