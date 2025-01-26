<?php

namespace App\Controller\Admin;

use App\Entity\Primelevel;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class PrimelevelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Primelevel::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('start', 'De'),
            IntegerField::new('end', 'A'),
            NumberField::new('percentage', 'Pourcentage'),
        ];
    }

}
