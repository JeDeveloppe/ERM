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
        // ->add('city', SearchShopAutocompleteField::class)
        ->add('city', EntityType::class, [
                'class' => City::class,
                'placeholder' => 'Choisir une ville...',
                // 'choice_label' => function (City $ville) {
                //     return $ville->getPostalCode().' - '.$ville->getName();
                // },
                'autocomplete' => true,
                // 'attr' => [
                //     'class' => 'form-control p-0',
                // ],
            ])
        ->add('submit', SubmitType::class, [
            'label' => 'Rechercher',
            'attr' => [
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}