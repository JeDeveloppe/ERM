<?php

namespace App\Form;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchShopsByCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('city', SearchShopsByCityAutocompleteField::class)
        // ->add('city', EntityType::class, [
        //     'class' => City::class,
        //     'query_builder' => function (CityRepository $cityRepository) {
        //         return $cityRepository->createQueryBuilder('c')
        //             ->orderBy('c.name', 'ASC');
        //     },
        //     'choice_label' => 'name',
        //     'placeholder' => 'Choisir une ville',
        //     'attr' => [
        //         'class' => 'form-control'
        //     ],
        //     'autocomplete' => true
        // ])
        ->add('submit', SubmitType::class, [
            'label' => 'Rechercher',
            'attr' => [
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}