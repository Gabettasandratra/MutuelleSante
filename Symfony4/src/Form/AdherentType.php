<?php

namespace App\Form;

use App\Entity\Adherent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AdherentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codeMutuelle')
            ->add('nom')
            ->add('prenom')
            ->add('sexe')
            ->add('dateNaissance', DateType::class)
            ->add('profession')
            ->add('salaire')
            ->add('adresse')
            ->add('telephone1')
            ->add('telephone2')
            ->add('email')
            ->add('dateInscription', DateType::class)
            ->add('photo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
        ]);
    }
}
