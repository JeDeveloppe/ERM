<?php

namespace App\Controller\Admin;

use App\Entity\ZoneErm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ZoneErmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ZoneErm::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la zone:'),
            AssociationField::new('regionErm', 'Région ERM:'),
            ColorField::new('territoryColor', 'Couleur de la zone:'),
            AssociationField::new('manager', 'Manager de la zone:'),
            AssociationField::new('shops', 'Nombre de centre(s)')->onlyOnIndex(),
            AssociationField::new('shops', 'Les centres de la zone')->onlyOnForms(),
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des zones ERM')
            ->setPageTitle('new', 'Nouvelle zone ERM')
            ->setPageTitle('edit', 'Modifier la zone ERM')
            ->showEntityActionsInlined();
    }
}
