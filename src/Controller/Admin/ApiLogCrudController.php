<?php

namespace App\Controller\Admin;

use App\Entity\ApiLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ApiLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ApiLog::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('service', 'Service:'),
            TextField::new('endPoint', 'End point:'),
            NumberField::new('duration', 'Durée:'),
            TextField::new('status', 'Statut:'),
            DateTimeField::new('loggedAt', 'Date:')->setFormat('dd/MM/yyyy HH:mm:ss'),
            AssociationField::new('user', 'Utilisateur:'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC']);
    }
}
