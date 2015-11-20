<?php

namespace App\Form;

use App\Entity\Exercice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentYear = (int) date('Y');
        $choices = [];
        for ($i=0; $i < 2; $i++) { 
            $value = (string) $currentYear++;
            $choices[$value] = $value;
        }
        
        $builder
            ->add('annee', ChoiceType::class, [
                'choices'  => $choices
            ])
            ->add('cotNouveau', NumberType::class)
            ->add('cotAncien', NumberType::class)
            ->add('dateDebut', DateType::class)
            ->add('dateFin', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Exercice::class,
        ]);
    }
}
