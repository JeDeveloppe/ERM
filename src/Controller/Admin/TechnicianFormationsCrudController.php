<?php

namespace App\Controller\Admin;

use App\Entity\TechnicianFormations;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TechnicianFormationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TechnicianFormations::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Détails'),
                TextField::new('name', 'Nom'),
                ColorField::new('color', 'Couleur'),
            FormField::addTab('Mise à jour'),
                AssociationField::new('updatedBy', 'Mise à jour par:')->setDisabled(true)->onlyOnForms(),
                DateTimeField::new('updatedAt', 'Mise à jour le:')->setDisabled(true)->onlyOnForms(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des formations')
            ->setPageTitle('new', 'Nouvelle formation')
            ->setPageTitle('edit', 'Modifier une formation')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof TechnicianFormations) {
            $entityInstance->setUpdatedBy($this->getUser());
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
