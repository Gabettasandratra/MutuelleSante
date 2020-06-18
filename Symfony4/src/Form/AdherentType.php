<?php

namespace App\Form;

use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Garantie;

class AdherentType extends AbstractType
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
            ->add('dateNaissance', BirthdayType::class)
            ->add('profession')
            ->add('salaire')
            ->add('adresse')
            ->add('telephone1')
            ->add('telephone2')
            ->add('email', EmailType::class)
            ->add('dateInscription', DateType::class)
            ->add('photo', FileType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('garantie', EntityType::class, [
                'required' => true,
                'class' => Garantie::class,
                'choice_label' => 'nom'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
}
