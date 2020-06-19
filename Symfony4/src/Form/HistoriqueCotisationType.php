<?php

namespace App\Form;

use App\Entity\HistoriqueCotisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Adherent;
use App\Entity\Exercice;

class HistoriqueCotisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePaiement')
            ->add('adherent', EntityType::class, [
                'required' => true,
                'class' => Adherent::class,
                'choice_label' => 'nom'
            ])
            ->add('exercice', EntityType::class, [
                'required' => true,
                'class' => Exercice::class,
                'choice_label' => 'annee',
                'label' => 'Cotisation annuelle'
            ])
            ->add('montant')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueCotisation::class,
        ]);
    }
}
