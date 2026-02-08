<?php

namespace App\Form;

use App\Entity\Collaborator;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollaboratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'ex: Jean']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'ex: DUPONT']
            ])
            ->add('vacationInitial', IntegerType::class, [
                'label' => 'CP Initial',
                'attr' => ['min' => 0],
                'help' => 'Nombre de jours de congés payés hors ancienneté'
            ])
            ->add('rttInitial', IntegerType::class, [
                'label' => 'RTT Initial',
                'attr' => ['min' => 0]
            ])
            ->add('seniorityLeaveInitial', IntegerType::class, [
                'label' => 'Jours d\'ancienneté',
                'attr' => ['min' => 0]
            ])
            ->add('recoveryBalanceInitial', IntegerType::class, [
                'label' => 'Solde Récupération (JTT)',
                'attr' => ['min' => 0]
            ])
            // On ne demande pas le 'owner' si c'est l'utilisateur connecté qui crée ses collaborateurs
            // On ne demande pas 'updatedAt', c'est géré par @ORM\HasLifecycleCallbacks
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collaborator::class,
        ]);
    }
}