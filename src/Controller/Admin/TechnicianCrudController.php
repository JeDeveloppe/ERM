<?php

namespace App\Controller\Admin;

use App\Entity\Technician;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use phpDocumentor\Reflection\Types\Boolean;

class TechnicianCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Technician::class;
    }

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('shop', 'Centre ERM:')->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                $queryBuilder
                    ->orderBy('entity.cm', 'ASC')
                )
            ->setFormTypeOptions(['placeholder' => 'Choisir un centre...']),
            AssociationField::new('controledByCgo', 'Sous CGO:')->setRequired(true)->setFormTypeOptions(['placeholder' => 'Choisir un CGO...']),
            BooleanField::new('isTelematic', 'Tech.télématique')->onlyOnIndex()->setDisabled(true),
            BooleanField::new('isTelematic', 'Tech.télématique')->onlyOnForms(),
            TextField::new('name', 'Nom'),
            TextField::new('firstName', 'Prénom'),
            TextField::new('email', 'Email'),
            TextField::new('phone', 'Téléphone'),
            AssociationField::new('vehicle', 'Véhicule')
                ->hideOnIndex()
                ->setFormTypeOptions(['placeholder' => 'Choisir un véhicule...'])
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                    $queryBuilder
                        ->orderBy('entity.name', 'ASC')
                    ),
            AssociationField::NEW('technicianFormations', 'Formations')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                    $queryBuilder
                        ->orderBy('entity.name', 'ASC')
                    ),
            TextEditorField::new('informations', 'Informations')->hideOnIndex(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['name' => 'ASC']);
    }

    // public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    // {
    //     $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

    //     //Only telematic technicians in this controller
    //     $queryBuilder->where('entity.isTelematic = :true')
    //     ->setParameter('true', true);

    //     return $queryBuilder;
    // }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isTelematic')
            ->add('technicianFormations')
            ->add('shop')
            ->add('vehicle')
            ->add('controledByCgo')
        ;
    }
    
}
