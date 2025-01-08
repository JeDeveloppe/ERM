<?php

namespace App\Controller\Admin;

use App\Entity\TelematicArea;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;

class TelematicAreaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TelematicArea::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('cgo', 'CGO:'),
            ColorField::new('territoryColor', 'Couleur de la zone:'),
            AssociationField::new('departments', 'Nombre de departements:')->onlyOnIndex(),
            AssociationField::new('departments', 'Les departements de la zone')->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des zones télématiques')
            ->setPageTitle('new', 'Nouvelle zone télématique')
            ->setPageTitle('edit', 'Modifier une zone télématique')
            ->showEntityActionsInlined();
    }
}
