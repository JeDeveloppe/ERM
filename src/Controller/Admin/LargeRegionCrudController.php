<?php

namespace App\Controller\Admin;

use App\Entity\LargeRegion;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LargeRegionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LargeRegion::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom:'),
            TextField::new('code', 'Code:'),
            TextField::new('slug', 'Slug:')->hideOnIndex(),
            ColorField::new('color', 'Couleur:'),
            AssociationField::new('departments', 'Départements:')->hideOnIndex(),
        ];
    }
}
