<?php

namespace App\Form;

use Dom\Entity;
use App\Entity\Technician;
use Doctrine\ORM\EntityRepository;
use App\Entity\TechnicianFormations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchTechnicianFormationsTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', EntityType::class, [
                'class' => TechnicianFormations::class,
                'choice_label' => 'name',
                'placeholder' => 'Afficher les techniciens avec la formation...',
                'attr' => [
                    'class' => 'form-control'   
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f') // 'g' is an alias for the Genre entity
                        ->orderBy('f.name', 'ASC');     // Order by the 'name' property in ascending order
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TechnicianFormations::class,
        ]);
    }
}
