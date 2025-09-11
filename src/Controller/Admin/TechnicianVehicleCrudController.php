<?php

namespace App\Controller\Admin;

use App\Entity\TechnicianVehicle;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TechnicianVehicleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicianVehicle::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Détails'),
                TextField::new('name', 'Nom'),
            FormField::addTab('Mise à jour'),
                AssociationField::new('updatedBy', 'Mise à jour par:')->setDisabled(true)->onlyOnForms(),
                DateTimeField::new('updatedAt', 'Mise à jour le:')->setDisabled(true)->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des véhicules')
            ->setPageTitle('new', 'Nouveau véhicule')
            ->setPageTitle('edit', 'Modifier un véhicule')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof TechnicianVehicle) {
            $entityInstance->setUpdatedBy($this->getUser());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
