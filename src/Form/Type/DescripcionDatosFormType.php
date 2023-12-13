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
 * Descripción: Es la clase la que define el formulario de la descripcion del conjunto datos 
 * Al ser una apirest lo que hace es definir la estructura de entrada de los datos         
 */
class DescripcionDatosFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('denominacion', TextType::class)
            ->add('identificacion', TextType::class)
            ->add('descripcion', TextType::class)
            ->add('frecuenciaActulizacion', TextType::class)
            ->add('fechaInicio', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'Y-MM-dd',
                'attr' => [
                    'class' => 'combinedPickerInput',
                ],
                'label' => 'form.specialOffer.fechaInicio',
                'translation_domain' => 'Default',
                'required' => false,
            ])
            ->add('fechaFin', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'Y-MM-dd',
                'attr' => [
                    'class' => 'combinedPickerInput',
                ],
                'label' => 'form.specialOffer.fechaFin',
                'translation_domain' => 'Default',
                'required' => false,
            ])
            ->add('territorio', TextType::class)
            ->add('instancias', TextType::class)
            ->add('organoResponsable', TextType::class)
            ->add('finalidad', TextType::class)
            ->add('condiciones', TextType::class)
            ->add('vocabularios',TextType::class)
            ->add('servicios', TextType::class)
            ->add('etiquetas', TextType::class)
            ->add('estructura', TextType::class)
            ->add('estructuraDenominacion', TextType::class)
            ->add('licencias', TextType::class)
            ->add('formatos', TextType::class)
            ->add('usuario', TextType::class)
            ->add('sesion', TextType::class)
            ->add('estado', TextType::class)
            ->add('estadoAlta', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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