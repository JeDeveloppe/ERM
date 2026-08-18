<?php

namespace App\Controller\Admin;

use App\Entity\ZoneErm;
use App\Entity\RoleErm;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ZoneErmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ZoneErm::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom de la zone:'),
            AssociationField::new('regionErm', 'Région ERM:'),
            ColorField::new('territoryColor', 'Couleur de la zone:'),
            AssociationField::new('manager', 'Manager de la zone (RZ, AO ou DR):')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) =>
                        $queryBuilder
                            ->join('entity.roles', 'r')
                            ->where('r.name IN (:roleNames)')
                            ->setParameter('roleNames', [RoleErm::RZ, RoleErm::AO, RoleErm::DR])
                    )
                ->setFormTypeOptions(['placeholder' => 'Séléctionner un manager', 'by_reference' => false]),
            AssociationField::new('shops', 'Nombre de centre(s)')->onlyOnIndex(),
            AssociationField::new('shops', 'Les centres de la zone')->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des zones ERM')
            ->setPageTitle('new', 'Nouvelle zone ERM')
            ->setPageTitle('edit', 'Modifier la zone ERM')
            ->setSearchFields(['name'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('regionErm')
            // Pas de vrai champ "classe" sur la zone : VL/MV fait partie du nom
            // ("Zone VL Xxx" / "Zone MV Xxx"). Le filtre texte par défaut
            // d'EasyAdmin permet de taper VL ou MV avec l'opérateur "contient".
            ->add('name')
        ;
    }
}
