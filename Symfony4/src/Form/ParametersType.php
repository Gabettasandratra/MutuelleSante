<?php

namespace App\Form;

use App\Entity\Compte;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('compte_cotisation', EntityType::class, [
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere('c.classe = \'7-COMPTES DE PRODUITS\'')
                            ->andWhere('length(c.poste) = 6')
                            ->orderBy('c.poste', 'ASC');
                },
                'choice_label' => function ($c) {
                    return $c->getPoste().' | '.$c->getTitre();
                },
            ])
            ->add('label_cotisation')
            ->add('compte_prestation', EntityType::class, [
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere('c.classe = \'6-COMPTES DE CHARGES\'')
                            ->andWhere('length(c.poste) = 6')
                            ->orderBy('c.poste', 'ASC');
                },
                'choice_label' => function ($c) {
                    return $c->getPoste().' | '.$c->getTitre();
                },
            ])
            ->add('label_prestation')
            ->add('percent_prestation', PercentType::class)
            ->add('percent_rembourse_prestation', PercentType::class)
            ->add('plafond_prestation', NumberType::class)
            ->add('compte_dette_prestation', EntityType::class, [
                'class' => Compte::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                            ->andWhere('c.classe = \'4-COMPTES DE TIERS\'')
                            ->andWhere('length(c.poste) = 6')
                            ->orderBy('c.poste', 'ASC');
                },
                'choice_label' => function ($c) {
                    return $c->getPoste().' | '.$c->getTitre();
                },
            ])
            ->add('soins_prestation', TextareaType::class, [
                'attr' => [
                    'style' => 'display:none;'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
