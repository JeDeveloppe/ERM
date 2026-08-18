<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class SearchCityAutocompleteFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => City::class,
            'placeholder' => 'Choisir une ville...',
            'attr' => [
                'class' => 'form-control',
            ],
            'choice_label' => function (City $ville) {
                $department = $ville->getDepartment();
                $departmentLabel = $department ? ' — '.$department->getName() : '';

                return $ville->getName().' ('.$ville->getPostalCode().')'.$departmentLabel;
            },

            // Nom et code postal uniquement : évite de chercher dans des champs
            // sans intérêt pour l'utilisateur (slug, code insee...).
            'searchable_fields' => ['name', 'postalCode'],

            // Plus de résultats affichés par défaut (10 était trop court pour
            // des noms de villes courants comme "Saint-..." ou "Paris").
            'max_results' => 20,

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

}
