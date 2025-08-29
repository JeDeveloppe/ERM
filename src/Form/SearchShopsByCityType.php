<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchShopsByCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $options = $options['formOptions'];

        $builder
        ->add('city', SearchShopsByCityAutocompleteField::class)
        ->add('options', ChoiceType::class, [
            'label' => false,
            'choices' => $options['choices'],
            'mapped' => false,
            'placeholder' => $options['placeholder'],
            'attr' => [
                'class' => 'form-control mb-3',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formOptions' => null
        ]);
    }
}