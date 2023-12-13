<?php

namespace App\Form\Type;

use App\Form\Model\SoporteDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/*
 * Descripción: Es la clase la que define el formulario de soporte ayuda
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */
class SoporteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tipoPeticion', TextType::class)
            ->add('titulo', TextType::class)
            ->add('descripcion', TextType::class)
            ->add('nombre', TextType::class)
            ->add('emailContacto', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SoporteDto::class,
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