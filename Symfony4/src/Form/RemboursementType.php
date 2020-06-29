<?php

namespace App\Form;

use App\Entity\Remboursement;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RemboursementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'format' => 'ddMMMMyyyy',
                'label' => 'Date de paiement',
            ])
            ->add('montant', NumberType::class, [
                'attr' => [
                    'readonly' => 'readonly'
                ]
            ])
            ->add('moyen', ChoiceType::class, [
                'label' => 'Methode de paiement',
                'choices'  => [
                    'Chèque' => 'Chèque',
                    'Virement bancaire' => 'Virement bancaire',
                    'Espèce' => 'Espèce',
                ]
            ])
            ->add('reference')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Remboursement::class,
        ]);
    }
}
