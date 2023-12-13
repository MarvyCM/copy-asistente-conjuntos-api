<?php

namespace App\Service\Processor;

use App\Entity\DescripcionDatos;
use App\Form\Model\DescripcionDatosDto;
use App\Form\Type\DescripcionDatosWorkFlowFormType;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\MailTool;
use App\Enum\EstadoDescripcionDatosEnum;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class DescripcionDatosWorkFlowFormProcessor
{
    private $descripcionDatosManager;
    private $formFactory;
    private $mailtool;
    public function __construct(
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        MailTool $mailtool
    ) {
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->mailtool = $mailtool;
    }

    public function __invoke(DescripcionDatos $descripcionDatos, 
                             Request $request): array
    {
        $estadoActual = $descripcionDatos->getEstado();
        $descripcionDatosDto = DescripcionDatosDto::createFromDescripcionDatos($descripcionDatos);
                 // creo el formulario vacío con los datos actuales
        $form = $this->formFactory->create(DescripcionDatosWorkFlowFormType::class, $descripcionDatosDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'El fomulario no ha sido enviado'];
        }
        if ($form->isValid()) {   

            $descripcionDatos->setUsuario($descripcionDatosDto->usuario);
            $descripcionDatos->setSesion($descripcionDatosDto->sesion);
            //estos estados cambian respecto al que viene del formulario porque estan en el caso de uso de solicitud de modificacion
            if ($estadoActual == EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION) {
                if   ($descripcionDatosDto->estado == EstadoDescripcionDatosEnum::VALIDADO) {
                    $descripcionDatosDto->estado = EstadoDescripcionDatosEnum::EN_CORRECCION;
                } else if  ($descripcionDatosDto->estado == EstadoDescripcionDatosEnum::DESECHADO) {
                    $descripcionDatosDto->estado = EstadoDescripcionDatosEnum::VALIDADO;
                }
            }
            //todos los demás casos de uso el estado final es el mismo que el del formulario.
            //guardo
            $descripcionDatos->setEstado($descripcionDatosDto->estado);
            $descripcionDatos->updatedTimestamps();
            
            $this->descripcionDatosManager->save($descripcionDatos);
            $this->descripcionDatosManager->reload($descripcionDatos);
            $this->mailtool->sendEmailWorkFlow($descripcionDatos, $descripcionDatosDto->descripcion);
            /*
            switch ($descripcionDatosDto->estado) {
                case EstadoDescripcionDatosEnum::EN_ESPERA:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::DESECHADO:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::VALIDADO:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
                case EstadoDescripcionDatosEnum::EN_CORRECCION:
                    $this->mailtool->sendEmail($descripcionDatos, $descripcionDatosDto->descripcion);
                    break;
            }
            */
            return [$descripcionDatos, null];
            
        }
        return [null, $form];
    }
}

