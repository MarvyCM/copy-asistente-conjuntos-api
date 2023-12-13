<?php

namespace App\Form\Type;

use App\Form\Model\UserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Descripción: Es la clase la que define el formulario del registro usuario y solictud JWT 
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', TextType::class)
            ->add('roles', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserDto::class,
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function getName()
    {
        return '';
    }
}