<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')            
            ->add('nom')
            ->add('prenom')
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->add('fonction', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'administrateur',
                    'Agent comptable' => 'comptable',
                    'Gestionnaire mutuelle' => 'gestionnaire',
                ]
            ])
            ->add('photo', FileType::class, [
                'mapped' => false,
                'required' => false
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
