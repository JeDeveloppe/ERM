<?php

namespace App\Controller\Admin;

use App\Entity\TelematicArea;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TelematicAreaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TelematicArea::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Détails'),
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
                ->setFormTypeOption('by_reference', false),
                // ->setQueryBuilder(
                //     fn(QueryBuilder $queryBuilder) => 
                //     $queryBuilder
                //     ->orderBy('entity.name', 'ASC')
                //     )
            FormField::addTab('Mise à jour'),
                AssociationField::new('updatedBy', 'Mise à jour par:')->setDisabled(true)->onlyOnForms(),
                DateTimeField::new('updatedAt', 'Mise à jour le:')->setDisabled(true)->onlyOnForms(),
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof TelematicArea) {
            $entityInstance->setUpdatedBy($this->getUser());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
