<?php

namespace App\Controller\Admin;

use App\Entity\Manager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DomCrawler\Form;

class ManagerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Manager::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Détails'),
                TextField::new('firstName'),
                TextField::new('lastName'),
                TextField::new('phone'),
                TextField::new('email'),

            FormField::addTab('Manager de'),
                AssociationField::new('regionErm'),
                AssociationField::new('zoneErm'),
                AssociationField::new('shop'),
        ];
    }

}
