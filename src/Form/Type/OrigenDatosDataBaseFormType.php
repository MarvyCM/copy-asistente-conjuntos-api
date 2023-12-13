<?php

namespace App\Form\Type;

use App\Form\Model\OrigenDatosDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/*
 * Descripción: Es la clase la que define el formulario del origen de datos formato base datos del conjunto datos 
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */
class OrigenDatosDataBaseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class)
            ->add('idDescripcion', TextType::class)
            ->add('tipoOrigen', TextType::class)
            ->add('tipoBaseDatos', TextType::class)
            ->add('host', TextType::class)
            ->add('puerto', TextType::class)
            ->add('servicio', TextType::class)
            ->add('esquema', TextType::class)
            ->add('tabla', TextType::class)
            ->add('usuarioDB', TextType::class)
            ->add('contrasenaDB', TextType::class)
            ->add('usuario', TextType::class)
            ->add('sesion', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['database'],
            'data_class' => OrigenDatosDto::class,
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