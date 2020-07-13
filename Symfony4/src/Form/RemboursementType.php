<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\Remboursement;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
            ->add('tresorerie', EntityType::class, [
                'label' => 'Mode de paiement',
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere('c.isTresor = true')
                            ->andWhere('c.acceptOut = true')
                            ->orderBy('c.poste', 'ASC');
                },
                'choice_label' => 'titre',
            ])
            ->add('reference')
            ->add('remarque', TextareaType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Remboursement::class,
        ]);
    }
}
