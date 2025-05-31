<?php

namespace App\Controller\Admin;

use App\Entity\TechnicalAdvisor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TechnicalAdvisorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicalAdvisor::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('manager', 'Manager:'),
            AssociationField::new('attachmentCenter', 'Centre de rattachement:'),
            ColorField::new('zoneColor', 'Couleur de sa zone:'),
            TextField::new('firstName', 'Prénom:'),
            TextField::new('lastName', 'Nom:'),
            TextField::new('phone', 'Tel:'),
            TextField::new('email', 'Email:'),
            AssociationField::new('workForShops', 'Inspections pour les centres de:')
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des CT')
            ->setPageTitle('new', 'Nouveau CT')
            ->setPageTitle('edit', 'Modifier le CT')
            ->setSearchFields(['manager.firstName', 'lastName', 'firstName', 'phone', 'email'])
            ->showEntityActionsInlined();
    }
}
