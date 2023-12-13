<?php

namespace App\Service\Processor;

use App\Entity\DescripcionDatos;
use App\Form\Model\DescripcionDatosDto;
use App\Form\Type\DescripcionDatosFormType;
use App\Service\Manager\DescripcionDatosManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class DescripcionDatosFormProcessor
{
    private $descripcionDatosManager;
    private $formFactory;

    public function __construct(
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory
    ) {
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(DescripcionDatos $descripcionDatos, 
                             Request $request): array
    {
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
            // creo el formulario vacío con los datos actuales
        $form = $this->formFactory->create(DescripcionDatosFormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'El fomulario no ha sido enviado'];
        }
        if ($form->isValid()) {  
             //guardo 
            #paso 1 
            $descripcionDatos->setDenominacion($descripcionDatosDto->denominacion);
            $descripcionDatos->setIdentificacion($descripcionDatosDto->identificacion);
            $descripcionDatos->setDescripcion($descripcionDatosDto->descripcion);
            $descripcionDatos->setTerritorio($descripcionDatosDto->territorio);
            $descripcionDatos->setFrecuenciaActulizacion($descripcionDatosDto->frecuenciaActulizacion);
            $descripcionDatos->setFechaInicio($descripcionDatosDto->fechaInicio);
            $descripcionDatos->setFechaFin($descripcionDatosDto->fechaFin);
            $descripcionDatos->setInstancias($descripcionDatosDto->instancias);
        
      
            #paso 2
            $descripcionDatos->setOrganoResponsable($descripcionDatosDto->organoResponsable);
            $descripcionDatos->setFinalidad($descripcionDatosDto->finalidad);
            $descripcionDatos->setCondiciones($descripcionDatosDto->condiciones);
            $descripcionDatos->setVocabularios($descripcionDatosDto->vocabularios);
            $descripcionDatos->setServicios($descripcionDatosDto->servicios);


            #paso 3
            $descripcionDatos->setEstructura($descripcionDatosDto->estructura);
            $descripcionDatos->setEstructuraDenominacion($descripcionDatosDto->estructuraDenominacion);
            $descripcionDatos->setLicencias($descripcionDatosDto->licencias);
            $descripcionDatos->setFormatos($descripcionDatosDto->formatos);
            $descripcionDatos->setEtiquetas($descripcionDatosDto->etiquetas);

            $descripcionDatos->setUsuario($descripcionDatosDto->usuario);
            $descripcionDatos->setSesion($descripcionDatosDto->sesion);
            $descripcionDatos->setEstado($descripcionDatosDto->estado);
            $descripcionDatos->setEstadoAlta($descripcionDatosDto->estadoAlta);
            $descripcionDatos->updatedTimestamps();
            
            $this->descripcionDatosManager->save($descripcionDatos);
            $this->descripcionDatosManager->reload($descripcionDatos);

            return [$descripcionDatos, null];
            
        }
        return [null, $form];
    }
}