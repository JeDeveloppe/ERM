<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\RoleErm;
use App\Repository\RoleErmRepository;
use App\Repository\TechnicianVehicleRepository;
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

class PersonCtCrudController extends AbstractCrudController
{
    public function __construct(
        private TechnicianVehicleRepository $technicianVehicleRepository,
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
            AssociationField::new('workForShops', 'Inspections pour les centres de:'),
            ColorField::new('zoneColor', 'Couleur de sa zone:')->onlyOnForms(),
            AssociationField::new('manager', 'Manager:'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('CT')
            ->setEntityLabelInPlural('CT')
            ->setPageTitle('index', 'Liste des CT')
            ->setPageTitle('new', 'Nouveau CT')
            ->setPageTitle('edit', 'Modifier le CT')
            ->setSearchFields(['manager.firstName', 'name', 'firstName', 'phone', 'email'])
            ->showEntityActionsInlined();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder->join('entity.roles', 'r')
            ->andWhere('r.name = :roleName')
            ->setParameter('roleName', RoleErm::CT);

        return $queryBuilder;
    }

    public function createEntity(string $entityFqcn)
    {
        $person = new Person();
        $person->setVehicle($this->technicianVehicleRepository->findOneBy(['name' => 'SANS VEHICULE']));

        $ctRole = $this->roleErmRepository->findOneBy(['name' => RoleErm::CT]);
        if ($ctRole) {
            $person->addRole($ctRole);
        }

        return $person;
    }
}
