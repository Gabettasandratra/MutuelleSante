<?php

namespace App\Form;

use App\Entity\Compte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('poste')
            ->add('titre')
            ->add('categorie', ChoiceType::class, [
                'choices'  => [
                    'Compte de Bilan' => 'Bilan',
                    'Compte de Gestion' => 'Gestion',
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Actif | Charge' => true,
                    'Passif | Produit' => false,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
        ]);
    }
}
