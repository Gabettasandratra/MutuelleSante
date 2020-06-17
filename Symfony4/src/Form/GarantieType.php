<?php

namespace App\Form;

use App\Entity\Garantie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\TypeCotisation;

class GarantieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('droitAdhesion')
            ->add('delaiRetard')
            ->add('delaiReprise')
            ->add('periodeObservation')
            ->add('typeCotisation', EntityType::class, [
                'required' => true,
                'class' => TypeCotisation::class,
                'choice_label' => 'nom'
            ])
            ->add('montant1')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Garantie::class,
        ]);
    }
}
