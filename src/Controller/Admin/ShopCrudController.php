<?php

namespace App\Controller\Admin;

use App\Entity\Shop;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ShopCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Shop::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('cm','CM:'),
            AssociationField::new('zoneErm', 'Zone ERM:')->onlyOnForms(),
            AssociationField::new('shopClass', 'Classe:'),
            TextField::new('name', 'Nom:'),
            TextField::new('address', 'Adresse:')->onlyOnForms(),
            AssociationField::new('city', 'Ville:'),
            TextField::new('phone', 'Téléphone:'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des centres ERM')
            ->setPageTitle('new', 'Nouveau centre ERM')
            ->setPageTitle('edit', 'Modifier le centre ERM')
            ->showEntityActionsInlined();
    }
}
