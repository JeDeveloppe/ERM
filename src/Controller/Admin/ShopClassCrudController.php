<?php

namespace App\Controller\Admin;

use App\Entity\ShopClass;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ShopClassCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ShopClass::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la classe:'),
        ];
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des classes ERM')
            ->setPageTitle('new', 'Nouvelle classe ERM')
            ->setPageTitle('edit', 'Modifier la classe ERM')
            ->showEntityActionsInlined();
    }
}
