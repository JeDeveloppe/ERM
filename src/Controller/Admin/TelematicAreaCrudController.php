<?php

namespace App\Controller\Admin;

use App\Entity\TelematicArea;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class TelematicAreaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TelematicArea::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            CollectionField::new('cgos', 'CGO(s):'),
            AssociationField::new('cgos', 'CGO(s)')
                ->onlyOnForms()
                ->setFormTypeOption('by_reference', false)
                ->setQueryBuilder(
                    fn(QueryBuilder $queryBuilder) => 
                    $queryBuilder
                    ->where('entity.name LIKE :name')
                    ->setParameter('name', '%VI%')
                    ->orderBy('entity.name', 'ASC')
                    ),
            ColorField::new('territoryColor', 'Couleur de la zone:'),
            AssociationField::new('departments', 'Nombre de departements:')->onlyOnIndex(),
            AssociationField::new('departments', 'Les departements de la zone')
            ->autocomplete()
            ->onlyOnForms()
            ->setFormTypeOption('by_reference', false)
            // ->setQueryBuilder(
            //     fn(QueryBuilder $queryBuilder) => 
            //     $queryBuilder
            //     ->orderBy('entity.name', 'ASC')
            //     )
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des zones télématiques')
            ->setPageTitle('new', 'Nouvelle zone télématique')
            ->setPageTitle('edit', 'Modifier une zone télématique')
            ->showEntityActionsInlined();
    }
}
