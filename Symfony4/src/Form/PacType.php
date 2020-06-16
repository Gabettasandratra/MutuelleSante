<?php

namespace App\Form;

use App\Entity\Pac;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PacType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codeMutuelle')
            ->add('nom')
            ->add('prenom')
            ->add('sexe', ChoiceType::class, [
                'choices'  => [
                    'Masculin' => 'Masculin',
                    'Feminin' => 'Feminin',
                ]
            ])
            ->add('dateNaissance', DateType::class)
            ->add('parente', ChoiceType::class, [
                'choices'  => [
                    'Conjoint' => 'Conjoint',
                    'Fils' => 'Fils',
                    'Fille' => 'Fille',
                    'Autre' => 'Autre',
                ]
            ])
            ->add('dateEntrer', DateType::class)
            ->add('photo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pac::class,
        ]);
    }
}
