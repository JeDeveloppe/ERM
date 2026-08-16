<?php

namespace App\Controller\Admin;

use App\Entity\RegionErm;
use App\Entity\RoleErm;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RegionErmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RegionErm::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la région ERM:'),
            ColorField::new('territoryColor', 'Couleur de la région:'),
            AssociationField::new('regionManagers', 'Manager de la région (DR):')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) =>
                        $queryBuilder
                            ->join('entity.roles', 'r')
                            ->where('r.name = :roleName')
                            ->setParameter('roleName', RoleErm::DR)
                    )
                ->setFormTypeOptions(['placeholder' => 'Séléctionner un manager', 'by_reference' => false]),
            AssociationField::new('zoneErms', 'Nombre de zone(s):')->onlyOnIndex(),
            AssociationField::new('zoneErms', 'Zones de la région:')
                ->onlyOnForms()
                ->setFormTypeOptions(['by_reference' => false]),
            AssociationField::new('departments', 'Nombre de département(s):')->onlyOnIndex(),
            AssociationField::new('departments', 'Départements de la région:')
                ->onlyOnForms()
                ->setFormTypeOptions(['by_reference' => false])
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des régions ERM')
            ->setPageTitle('new', 'Nouvelle région ERM')
            ->setPageTitle('edit', 'Modifier la région ERM')
            ->showEntityActionsInlined();
    }
}
