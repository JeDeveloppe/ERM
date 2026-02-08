<?php

namespace App\Form;

use App\Entity\Collaborator;
use App\Entity\CollaboratorAbsence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollaboratorAbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('collaborator', EntityType::class, [
                'class' => Collaborator::class,
                'choice_label' => function(Collaborator $c) {
                    return $c->getFirstName() . ' ' . strtoupper($c->getLastName());
                },
                'label' => 'Collaborateur',
                'placeholder' => 'Sélectionnez un collaborateur', // Affiche une ligne vide au début
                'disabled' => $options['data']->getId() !== null,
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Congés Payés (CP)' => 'CP',
                    'RTT' => 'RTT',
                    'Jours de Récup (JTT)' => 'JTT',
                ],
                'label' => 'Type d\'absence',
                'placeholder' => 'Sélectionnez un type de repos', // Affiche une ligne vide au début
                'required' => true,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'required' => true
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollaboratorAbsence::class,
        ]);
    }
}