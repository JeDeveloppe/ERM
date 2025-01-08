<?php

namespace App\Controller\Admin;

use App\Entity\Cgo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class CgoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cgo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('cm'),
            TextField::new('name', 'Nom:'),
            TextField::new('address', 'Adresse:'),
            TextField::new('ZoneName','Nom de zone:'),
            AssociationField::new('shopsUnderControls')
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des cgos')
            ->setPageTitle('new', 'Nouveau cgo')
            ->setPageTitle('edit', 'Modifier le cgo')
            ->setDefaultSort(['cm' => 'ASC'])
            ->showEntityActionsInlined();
    }
}
