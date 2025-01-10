<?php

namespace App\Controller\Admin;

use App\Entity\Manager;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

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
                AssociationField::new('managerClass', 'Classe:'),
                TextField::new('lastName', 'Nom de famille:'),
                TextField::new('firstName', 'Prénom:'),
                TextField::new('phone', 'Téléphone:'),
                TextField::new('email', 'Emai:'),

            FormField::addTab('Manager de'),
                AssociationField::new('regionErm', 'Region ERM:')->onlyOnForms(),
                AssociationField::new('zoneErm', 'Zone ERM:')->onlyOnForms(),
                AssociationField::new('cgos', 'CGO(s):')->onlyOnForms()
                    ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                            $queryBuilder
                                ->orderBy('entity.name', 'ASC')
                        )
                    ->setFormTypeOptions(['placeholder' => 'Séléctionner un CGO', 'by_reference' => false, 'multiple' => true]),
                AssociationField::new('shops', 'Centre ERM:')->onlyOnForms()
                    ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                    $queryBuilder
                        ->orderBy('entity.name', 'ASC')
                    )
                    ->setFormTypeOptions(['placeholder' => 'Séléctionner un centre', 'by_reference' => false, 'multiple' => true]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des managers')
            ->setPageTitle('new', 'Nouveau manager')
            ->setPageTitle('edit', 'Modifier le manager')
            ->setSearchFields(['managerClass.name', 'lastName', 'firstName', 'phone', 'email', 'regionErm.name', 'zoneErm.name', 'shops.name'])
            ->showEntityActionsInlined();
    }
}
