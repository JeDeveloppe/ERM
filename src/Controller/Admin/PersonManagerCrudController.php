<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\RoleErm;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonManagerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('roleNames', 'Rôles:')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(fn ($value, Person $person) => implode(' ', array_map(
                    fn(RoleErm $role) => '<span class="badge rounded-pill me-1" style="background-color: '.htmlspecialchars($role->getColor() ?? '#6c757d').';">'.htmlspecialchars($role->getName()).'</span>',
                    $person->getRoles()->toArray()
                ))),
            AssociationField::new('roles', 'Rôles:')
                ->onlyOnForms()
                ->setFormTypeOptions(['by_reference' => false]),
            TextField::new('firstName', 'Prénom:'),
            TextField::new('name', 'Nom:'),
            TextField::new('phone', 'Tel:'),
            TextField::new('email', 'Email:'),
            AssociationField::new('regionErm', 'Région dirigée:')->hideOnIndex(),
            AssociationField::new('zoneErms', 'Zones gérées:')->hideOnIndex(),
            AssociationField::new('cgos', 'CGO gérés:')->hideOnIndex(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Manager')
            ->setEntityLabelInPlural('Managers')
            ->setPageTitle('index', 'Liste des managers')
            ->setPageTitle('new', 'Nouveau manager')
            ->setPageTitle('edit', 'Modifier le manager')
            ->setSearchFields(['name', 'firstName', 'phone', 'email'])
            ->showEntityActionsInlined();
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder->join('entity.roles', 'r')
            ->andWhere('r.name IN (:roleNames)')
            ->setParameter('roleNames', RoleErm::MANAGER_ROLES);

        return $queryBuilder;
    }
}
