<?php

namespace App\Controller\Admin;

use App\Entity\Department;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DepartmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Department::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du departement:'),
            TextField::new('code', 'Code du departement:')->setDisabled(true),
            TextField::new('slug', 'Slug du departement:'),
            TextField::new('simplemapCode', 'Code simplemap:')->setDisabled(true),
            AssociationField::new('telematicArea', 'Zone Télématique de:'),
            AssociationField::new('cities')
        ];
    }
}
