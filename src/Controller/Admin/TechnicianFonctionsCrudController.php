<?php

namespace App\Controller\Admin;

use App\Entity\TechnicianFonction;
use App\Entity\TechnicianFormations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Util\Color;

class TechnicianFonctionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicianFonction::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            ColorField::new('color', 'Couleur'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des fonctions')
            ->setPageTitle('new', 'Nouvelle fonction')
            ->setPageTitle('edit', 'Modifier une fonction')
            ->setDefaultSort(['name' => 'ASC']);
    }
}
