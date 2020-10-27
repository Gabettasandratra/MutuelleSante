<?php

namespace App\Form;

use App\Entity\Pac;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class PacType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codeMutuelle', TextType::class)
            ->add('nom')
            ->add('prenom')
            ->add('sexe', ChoiceType::class, [
                'choices'  => [
                    'Masculin' => 'Masculin',
                    'Feminin' => 'Feminin',
                ]
            ])
            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'datepicker-simple','autocomplete' => 'off','placeholder' => 'jj/mm/aaaa'],
                'html5' => false,
            ])
            ->add('cin')
            ->add('tel')
            ->add('parente', ChoiceType::class, [
                'choices'  => [
                    'Responsable' => 'Responsable',
                    'Membre' => 'Membre',
                    'Autre' => 'Autre',
                ]
            ])
            ->add('dateEntrer', DateType::class, [
                'data' => new \DateTime(),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'datepicker-simple','autocomplete' => 'off'],
                'html5' => false,
            ])
            ->add('photo', FileType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pac::class,
        ]);
    }
}
