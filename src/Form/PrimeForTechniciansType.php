<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PrimeForTechniciansType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                    'placeholder' => 'Saisir un nombre',
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
