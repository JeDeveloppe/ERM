<?php

namespace App\Controller\Admin;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Date;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations utilisateur'),
            TextField::new('email', 'Email'),
            TextField::new('password', 'Mot de passe')->onlyWhenCreating(),
            ArrayField::new('roles', 'Rôles'),
            FormField::addTab('Suivis utilisateur'),
            DateTimeField::new('createdAt', 'Créé le')->setFormat('dd/MM/yyyy HH:mm:ss')->onlyWhenUpdating()->setDisabled(true),
            DateTimeField::new('lastVisitAt', 'Dernière visite')->setFormat('dd/MM/yyyy HH:mm:ss')->onlyOnIndex()->setDisabled(true),
            DateTimeField::new('lastVisitAt', 'Dernière visite')->setFormat('dd/MM/yyyy HH:mm:ss')->onlyWhenUpdating()->setDisabled(true),
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('new', 'Nouvel utilisateur')
            ->setPageTitle('edit', 'Modifier un utilisateur')
            ->showEntityActionsInlined();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {

            $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

            $entityInstance
                ->setPassword($this->userPasswordHasher->hashPassword($entityInstance, $entityInstance->getPassword()))
                ->setCreatedAt($now)
                ->setLastVisitAt($now);

            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
