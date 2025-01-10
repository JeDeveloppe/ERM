<?php

namespace App\Controller\Admin;

use App\Entity\RegionErm;
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
            AssociationField::new('regionManagers', 'Manager de la zone:')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                        $queryBuilder
                            ->join('entity.managerClass', 'm')
                            ->where('m.name = :className')
                            ->setParameter('className', 'RCGO')
                            ->orderBy('entity.managerClass', 'ASC')
                    )
                ->setFormTypeOptions(['placeholder' => 'Séléctionner un manager', 'by_reference' => false]),
            AssociationField::new('zoneErms', 'Nombre de zone(s):')->onlyOnIndex(),
            AssociationField::new('zoneErms', 'Zones de la région:')->onlyOnForms()
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
