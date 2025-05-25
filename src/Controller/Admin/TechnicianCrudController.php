<?php

namespace App\Controller\Admin;

use App\Entity\Technician;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TechnicianCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Technician::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('shop', 'Centre ERM:'),
            AssociationField::new('controledByCgo', 'Sous CGO:'),
            TextField::new('name', 'Nom'),
            TextField::new('firstName', 'Prénom'),
            TextField::new('email', 'Email'),
            TextField::new('phone', 'Téléphone'),
            AssociationField::new('vehicle', 'Véhicule'),
            AssociationField::NEW('technicianFormations', 'Formations'),
            TextEditorField::new('informations', 'Informations'),
        ];
    }
    
}
