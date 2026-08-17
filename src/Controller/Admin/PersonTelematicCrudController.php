<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\RoleErm;
use App\Repository\RoleErmRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonTelematicCrudController extends AbstractCrudController
{
    public function __construct(
        private RoleErmRepository $roleErmRepository
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom:'),
            TextField::new('firstName', 'Prénom:'),
            TextField::new('phone', 'Tel:'),
            TextField::new('email', 'Email:'),
            AssociationField::new('shop', 'Centre de rattachement:'),
            AssociationField::new('vehicle', 'Véhicule:')->hideOnIndex(),
            AssociationField::new('technicianFormations', 'Formations:'),
            AssociationField::new('fonctions', 'Fonctions:')->hideOnIndex(),
            ColorField::new('zoneColor', 'Couleur de sa zone:')->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Technicien télématique')
            ->setEntityLabelInPlural('Techniciens télématiques')
            ->setPageTitle('index', 'Liste des techniciens télématiques')
            ->setPageTitle('new', 'Nouveau technicien télématique')
            ->setPageTitle('edit', 'Modifier le technicien télématique')
            ->setSearchFields(['name', 'firstName', 'phone', 'email'])
            ->showEntityActionsInlined();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder->join('entity.roles', 'r')
            ->andWhere('r.name = :roleName')
            ->setParameter('roleName', RoleErm::TECHNICIEN_TELEMATIQUE);

        return $queryBuilder;
    }

    public function createEntity(string $entityFqcn)
    {
        $person = new Person();

        $role = $this->roleErmRepository->findOneBy(['name' => RoleErm::TECHNICIEN_TELEMATIQUE]);
        if ($role) {
            $person->addRole($role);
        }

        return $person;
    }
}
