<?php

namespace App\Controller\Admin;

use App\Entity\Shop;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ShopCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Shop::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('cm'),
            AssociationField::new('zoneErm')->onlyOnForms(),
            AssociationField::new('shopClass'),
            TextField::new('name'),
            TextField::new('address')->onlyOnForms(),
            AssociationField::new('city'),
            TextField::new('phone'),
        ];
    }

}
