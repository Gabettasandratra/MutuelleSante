<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\Exercice;
use Doctrine\ORM\EntityRepository;

use App\Entity\HistoriqueCotisation;
use App\Repository\ExerciceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
                'label' => 'Date de paiement',
            ])
            ->add('montant')
            ->add('tresorerie', EntityType::class, [
                'label' => 'Mode de paiement',
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere('c.isTresor = true')
                            ->andWhere('c.acceptIn = true')
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
            'data_class' => HistoriqueCotisation::class,
            'adherent' => null 
        ]);
    }
}
