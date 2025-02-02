<?php

namespace App\Controller\Admin;

use App\Entity\Primelevel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Niveaux de prime')
            ->setPageTitle('edit', 'Modifier un niveau de prime')
            ->setPageTitle('detail', 'Détails du niveau de prime')
            ->setPageTitle('new', 'Ajouter un niveau de prime')
            ->setSearchFields(['start', 'end', 'percentage'])
            ->setDefaultSort(['start' => 'ASC']);
    }
}
