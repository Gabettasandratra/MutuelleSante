<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'data' => new \DateTime()
            ])
            ->add('montant', NumberType::class)
            ->add('libelle')
            ->add('piece')
            ->add('analytique')            
            ->add('compteDebit', EntityType::class, [
                'class' => Compte::class,
                'choice_label' => function ($c) {
                    return $c->getPoste().' | '.$c->getTitre();
                },
             ])
            ->add('compteCredit', EntityType::class, [
                'class' => Compte::class,
                'choice_label' => function ($c) {
                    return $c->getPoste().' | '.$c->getTitre();
                },
             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([          
            'data_class' => Article::class,
        ]);
    }
}
