<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Form\SearchCityAutocompleteFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchCityForRoadAssistanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('city', SearchCityAutocompleteFieldType::class)
        ->add('options', ChoiceType::class, [
            'label' => false,
            'choices' => ['Afficher les centres MV et MX les plus proches' => 'depannage'],
            'mapped' => false,
            'placeholder' => false,
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