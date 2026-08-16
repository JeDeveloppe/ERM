<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Fiche'),
                TextField::new('name', 'Nom'),
                TextField::new('firstName', 'Prénom'),
                TextField::new('email', 'Email'),
                TextField::new('phone', 'Téléphone'),
                AssociationField::new('roles', 'Rôles:')
                    ->setFormTypeOptions(['by_reference' => false]),

            FormField::addTab('Centre / technicien'),
                AssociationField::new('shop', 'Centre ERM:')->setQueryBuilder(fn(QueryBuilder $queryBuilder) =>
                    $queryBuilder
                        ->orderBy('entity.cm', 'ASC')
                    )
                ->setFormTypeOptions(['placeholder' => 'Choisir un centre...'])->hideOnIndex(),
                AssociationField::new('controledByCgo', 'Sous CGO:')->setFormTypeOptions(['placeholder' => 'Choisir un CGO...'])->hideOnIndex(),
                AssociationField::new('vehicle', 'Véhicule')
                    ->hideOnIndex()
                    ->setFormTypeOptions(['placeholder' => 'Choisir un véhicule...'])
                    ->setQueryBuilder(fn(QueryBuilder $queryBuilder) =>
                        $queryBuilder
                            ->orderBy('entity.name', 'ASC')
                        ),
                AssociationField::NEW('technicianFormations', 'Formations:')->hideOnIndex()
                    ->setQueryBuilder(fn(QueryBuilder $queryBuilder) =>
                        $queryBuilder
                            ->orderBy('entity.name', 'ASC')
                        ),
                AssociationField::new('fonctions', 'Fonction:')->hideOnIndex(),
                TextEditorField::new('informations', 'Informations')->hideOnIndex(),

            FormField::addTab('CT'),
                AssociationField::new('manager', 'Manager (AO):')->hideOnIndex(),
                ColorField::new('zoneColor', 'Couleur de sa zone:')->hideOnIndex(),
                AssociationField::new('workForShops', 'Inspections pour les centres de:')->hideOnIndex(),

            FormField::addTab('Rôle manager'),
                AssociationField::new('regionErm', 'Région dirigée (DR):')->hideOnIndex(),
                AssociationField::new('zoneErms', 'Zones gérées (AO/RZ/RAVL):')->hideOnIndex(),
                AssociationField::new('cgos', 'CGO gérés (RCGO):')->hideOnIndex(),

            FormField::addTab('Mise à jour'),
                AssociationField::new('updatedBy', 'Mise à jour par:')->setDisabled(true)->onlyOnForms(),
                DateTimeField::new('updatedAt', 'Mise à jour le:')->setDisabled(true)->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'firstName', 'phone', 'email', 'shop.name','shop.cm'])
            ->setPageTitle('index', 'Liste des personnes')
            ->setPageTitle('new', 'Nouvelle personne')
            ->setPageTitle('edit', 'Modifier une personne');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('roles')
            ->add('technicianFormations')
            ->add('fonctions')
            ->add('shop')
            ->add('vehicle')
            ->add('controledByCgo')
        ;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Person) {
            $entityInstance->setUpdatedBy($this->getUser());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
