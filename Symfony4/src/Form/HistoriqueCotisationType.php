<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\HistoriqueCotisation;
use App\Repository\ExerciceRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class HistoriqueCotisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('exercice', EntityType::class, [
                'label' => 'Paiement cotisation de l\'année',
                'class' => Exercice::class,
                'choice_label' => 'annee',
                'mapped' => false,
            ])
            ->add('datePaiement', DateType::class, [
                'format' => 'ddMMMMyyyy',
                'label' => 'Date de paiement',
            ])
            ->add('montant')
            ->add('moyen', ChoiceType::class, [
                'label' => 'Methode de paiement',
                'choices'  => [
                    'Chèque' => 'Chèque',
                    'Virement bancaire' => 'Virement bancaire',
                    'Espèce' => 'Espèce',
                ]
            ])
            ->add('reference')
            ->add('remarque')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueCotisation::class,
            'adherent' => null 
        ]);
    }
}
