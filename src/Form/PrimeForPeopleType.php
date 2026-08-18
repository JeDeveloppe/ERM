<?php

namespace App\Form;

use IntlNumberFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PrimeForPeopleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $versions = $options['versions'];

        $builder
            ->add('fullPs', IntegerType::class, [
                'label' => 'PS total:',
                'attr' => [
                    'placeholder' => 'Saisir un nombre',
                    'class' => 'form-control',
                ],
            ])
            ->add('divider', NumberType::class, [
                'label' => 'Diviseur:',
                'attr' => [
                    'placeholder' => 'Saisir un nombre (justif prime M-1)',
                    'class' => 'form-control',
                ],
            ])
            ->add('versionOld', ChoiceType::class, [
                'label' => 'Ancien barème (Comparaison) :',
                'choices' => $versions,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('compensationBonus', NumberType::class, [
                'label' => 'Bonus de compensation (€) :',
                'data' => 0, // Valeur par défaut
                'scale' => 0,
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'number',
                    'min' => 0,             // Empêche de descendre en dessous de 0 en HTML
                    'step' => 1,            // Force l'incrément par nombres entiers
                    'placeholder' => 'Ex: 69',
                ],
                'constraints' => [
                    new PositiveOrZero(message: 'Le bonus ne peut pas être négatif'),
                    new Type(type: 'integer', message: 'Veuillez saisir un nombre entier'),
                ],
            ])
            ->add('versionNew', ChoiceType::class, [
                'label' => 'Nouveau barème (Cible) :',
                'choices' => $versions,
                'attr' => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'versions' => [],
        ]);
    }
}
