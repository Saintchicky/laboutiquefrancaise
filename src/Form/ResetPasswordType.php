<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('new_password',RepeatedType::class,[
                'type'=>PasswordType::class,
                'invalid_message'=>'le mot de passe et la confirmation doivent être identique',
                'label'=>'Mon nouveau mot de passe',
                'required'=>true,
                'first_options'=>[
                    'label'=>'Nouveau Mot de passe',
                    'attr'=>[
                        'placeholder' => 'Merci de saisir votre nouveau mot de passe'
                        ]
                    ],
                'second_options'=>[
                    'label'=>'Confirmez votre mot de passe',
                    'attr'=>[
                            'placeholder' => 'Confirmer votre mot de passe'
                        ]
                    ]
            ])
            ->add('submit',SubmitType::class,[
                'label'=>'Mettre à jour votre mot de passe',
                    'attr'=>[
                            'class'=>'btn-block btn-info'
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
