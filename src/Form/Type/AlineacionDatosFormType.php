<?php

namespace App\Form\Type;

use App\Form\Model\AlineacionDatosDto;
use App\Enum\TipoOrigenDatosEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/*
 * Descripción: Es la clase la que define el formulario de la alineación de los datos 
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */

class AlineacionDatosFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class)
            ->add('alineacionEntidad', TextType::class)
            ->add('alineacionRelaciones', TextType::class)
            ->add('usuario', TextType::class)
            ->add('sesion', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AlineacionDatosDto::class,
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