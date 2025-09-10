<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use App\Entity\TechnicianFormations;
use Symfony\Component\Form\AbstractType;
use App\Form\SearchCityAutocompleteFieldType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchCityForTelematicInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('city', SearchCityAutocompleteFieldType::class)
        ->add('formations', EntityType::class, [
            'class' => TechnicianFormations::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'placeholder' => 'Afficher les techniciens avec la formation...',
            'attr' => [
                'class' => 'form-control'   
            ],
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f')
                    ->orderBy('f.name', 'ASC');
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formOptions' => null
        ]);
    }
}