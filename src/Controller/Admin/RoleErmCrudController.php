<?php

namespace App\Controller\Admin;

use App\Entity\RoleErm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RoleErmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RoleErm::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du rôle:'),
            ColorField::new('color', 'Couleur:'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des rôles ERM')
            ->setPageTitle('new', 'Nouveau rôle ERM')
            ->setPageTitle('edit', 'Modifier le rôle ERM')
            ->showEntityActionsInlined();
    }
}
