<?php

namespace App\Controller\Admin;

use App\Entity\RegionErm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RegionErmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RegionErm::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la région ERM:'),
            ColorField::new('territoryColor', 'Couleur de la région:'),
            AssociationField::new('zoneErms', 'Nombre de zone(s):')->onlyOnIndex(),
            AssociationField::new('zoneErms', 'Zones de la région:')->onlyOnForms()
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des régions ERM')
            ->setPageTitle('new', 'Nouvelle région ERM')
            ->setPageTitle('edit', 'Modifier la région ERM')
            ->showEntityActionsInlined();
    }
}
