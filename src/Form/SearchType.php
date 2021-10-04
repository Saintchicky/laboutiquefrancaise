<?php

namespace App\Form;

use App\Classe\Search;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('string',TextType::class,[
                'label'=> false,
                'required' => false,
                'attr'=>[
                    'placeholder' => 'Votre recherce ...',
                    'class'=>'form-control-sm'
                ]
            ])
            // importer en input l'entité directement
            ->add('categories', EntityType::class,[
                'label'=> false,
                'required' => false,
                'class'=> Category::class,
                // avoir des valeurs multiple
                'multiple'=> true,
                // vue en checkbox avec les différentes valeurs
                'expanded'=> true
                
            ])
            ->add('submit',SubmitType::class,[
                'label'=>"Filtrer",
                'attr'=>[
                    'class'=>'btn-block btn-info'
                ]
            ])
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            // ce n'est pas un post pour récup les recherches
            'method'=> 'GET',
            // désactive le token car juste renvois de données
            'crsf_protection' => false
        ]);
    }
    // retourne rien pr éviter d'avoir une url chargé"
    public function getBlockPrefix(){
        return '';
    }
}