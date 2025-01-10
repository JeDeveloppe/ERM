<?php

namespace App\Controller\Admin;

use App\Entity\Shop;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class ShopCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Shop::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('cm','CM:')->onlyWhenUpdating()->setDisabled()->setColumns(3),
            IntegerField::new('cm','CM:')->onlyWhenCreating()->onlyOnIndex(),
            AssociationField::new('shopClass', 'Classe:')->setColumns(3),
            AssociationField::new('zoneErm', 'Zone ERM:')->onlyOnForms()->setColumns(6),
            TextField::new('name', 'Nom:')->setColumns(6),
            TextField::new('address', 'Adresse:')->onlyOnForms()->setColumns(6),
            AssociationField::new('city', 'Ville:')->autocomplete()->setColumns(6),
            TextField::new('phone', 'Téléphone:')->setColumns(6),
            CollectionField::new('cgos', 'CGO(s):')->onlyOnIndex(),
            AssociationField::new('manager', 'Manager:')->autocomplete()->setColumns(6)->onlyOnForms(),
            AssociationField::new('cgos', 'CGO(s):')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => 
                        $queryBuilder
                            ->orderBy('entity.name', 'ASC')
                    )
                ->setFormTypeOptions(['placeholder' => 'Séléctionner un CGO', 'by_reference' => false, 'multiple' => true])
                ->onlyOnForms()
                ->setColumns(6),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des centres ERM')
            ->setPageTitle('new', 'Nouveau centre ERM')
            ->setPageTitle('edit', 'Modifier un centre ERM')
            ->setSearchFields(['name', 'address', 'phone', 'city.name','cm'])
            ->showEntityActionsInlined();
    }
}
