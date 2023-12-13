<?php

namespace App\Service\Processor;

use App\Entity\OrigenDatos;
use App\Form\Model\AlineacionDatosDto;
use App\Form\Type\AlineacionDatosFormType;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Base64JsonTool;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class AlineacionDatosDataFormProcessor
{

    private $origenDatosManager;
    private $formFactory;

    public function __construct(
        OrigenDatosManager $origenDatosManager,
        FormFactoryInterface $formFactory)
    {
        $this->origenDatosManager = $origenDatosManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(OrigenDatos $origendatos, Request $request): array
    {
        $origendatosDto = AlineacionDatosDto::createFromAlineacionDatosDatos($origendatos);
         // creo el formulario vacío con los datos actuales
        $form = $this->formFactory->create(AlineacionDatosFormType::class, $origendatosDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {  
            return [null,  'El fomulario no ha sido enviado'];
        }
        if ($form->isValid()) {
                //guardo
                $realcioonesJson = Base64JsonTool::getJosonBase64($origendatosDto->alineacionRelaciones);
                $origendatos->setAlineacionEntidad($origendatosDto->alineacionEntidad);
                $origendatos->setAlineacionRelaciones($realcioonesJson);
                $origendatos->setUsuario($origendatosDto->usuario);
                $origendatos->setSesion($origendatosDto->sesion);
                $origendatos->updatedTimestamps();
                $this->origenDatosManager->save($origendatos);
                $this->origenDatosManager->reload($origendatos);
                return [$origendatos, null]; 
        }     
        return [null, $form];
    }
} 