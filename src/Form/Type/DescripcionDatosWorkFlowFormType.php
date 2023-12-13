<?php

namespace App\Form\Type;

use App\Form\Model\DescripcionDatosDto;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/*
 * Descripción: Es la clase la que define el formulario del cambio de estado del conjunto datos 
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */
class DescripcionDatosWorkFlowFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('usuario', TextType::class)
            ->add('descripcion', TextType::class)
            ->add('sesion', TextType::class)
            ->add('estado', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['workflow'],
            'data_class' => DescripcionDatosDto::class,
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