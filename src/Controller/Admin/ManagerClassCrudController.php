<?php

namespace App\Controller\Admin;

use App\Entity\ManagerClass;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ManagerClassCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ManagerClass::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du statut:'),
        ];
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des status des managers')
            ->setPageTitle('new', 'Nouveau status des managers')
            ->setPageTitle('edit', 'Modifier status des managers')
            ->showEntityActionsInlined();
    }
}
