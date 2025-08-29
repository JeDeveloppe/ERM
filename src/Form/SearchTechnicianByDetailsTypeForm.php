<?php

// src/Form/SearchTechnicianFormationsTypeForm.php

namespace App\Form;

use App\Entity\TechnicianVehicle;
use App\Entity\TechnicianFonction;
use Doctrine\ORM\EntityRepository;
use App\Entity\TechnicianFormations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchTechnicianByDetailsTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour les formations
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
            ])
            // Champ pour les fonctions avec un nom unique
            ->add('fonctions', EntityType::class, [
                'class' => TechnicianFonction::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'placeholder' => 'Afficher les techniciens avec la fonction...',
                'attr' => [
                    'class' => 'form-control'   
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
            ])
            ->add('vehicles', EntityType::class, [
                'class' => TechnicianVehicle::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'placeholder' => 'Afficher les techniciens avec le véhicule...',
                'attr' => [
                    'class' => 'form-control'   
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');     
                }
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Le formulaire n'est pas lié à une entité unique, mais à un objet DTO ou null
            // J'ai mis null ici pour l'exemple, mais une classe DTO est la meilleure pratique
            'data_class' => null,
            'csrf_protection' => false,
        ]);
    }
}