<?php

namespace App\Form;

use App\Entity\Collaborator;
use App\Entity\CollaboratorAbsence;
use Symfony\Component\Form\AbstractType;
use App\Repository\CollaboratorRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CollaboratorAbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('collaborator', EntityType::class, [
                'class' => Collaborator::class,
                'choice_label' => function(Collaborator $c) {
                    return $c->getFirstName() . ' ' . strtoupper($c->getLastName());
                },
                'query_builder' => function (CollaboratorRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.owner = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.lastName', 'ASC');
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
            'user' => null
        ]);
    }
}