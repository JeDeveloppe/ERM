<?php

namespace App\Controller\Admin;

use App\Entity\TechnicianFormations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Util\Color;

class TechnicianFormationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicianFormations::class;
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
            ->setPageTitle('index', 'Liste des formations')
            ->setPageTitle('new', 'Nouvelle formation')
            ->setPageTitle('edit', 'Modifier une formation')
            ->setDefaultSort(['name' => 'ASC']);
    }
}
