<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Form\SearchCityAutocompleteFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCityForRoadAssistanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('city', SearchCityAutocompleteFieldType::class);
        // Pas d'autre champ "type de recherche" : ce formulaire ne sert qu'au
        // dépannage routier, il n'y a jamais eu qu'un seul choix possible.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formOptions' => null
        ]);
    }
}