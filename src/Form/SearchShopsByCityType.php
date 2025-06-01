<?php

namespace App\Form;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchShopsByCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('city', SearchShopsByCityAutocompleteField::class)
        ->add('options', ChoiceType::class, [
            'label' => false,
            'choices' => [
                'Afficher les centres les plus proches' => 'depannage',
                'Afficher les téchniciens télématiques les plus proches' => 'telematique',
            ],
            'mapped' => false,
            'placeholder' => 'Choisir une option...',
            'attr' => [
                'class' => 'form-control mb-3',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}