<?php

namespace App\Controller\Admin;

use App\Entity\Cgo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\DomCrawler\Form;

class CgoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cgo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations'),
                IntegerField::new('cm')->setDisabled(true),
                TextField::new('name', 'Nom:')->setDisabled(true),
                TextField::new('address', 'Adresse:'),
                AssociationField::new('city', 'Ville:')->autocomplete(),
                // TextField::new('ZoneName','Nom de zone:'),
                ColorField::new('territoryColor', 'Couleur de la zone:'),

            FormField::addTab('Les centres'),
            AssociationField::new('shopsUnderControls', 'Nombre de centres:')->onlyOnIndex(),
            AssociationField::new('shopsUnderControls', 'Les centres de la zone:')->onlyOnForms(),
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
