<?php

namespace App\Service\Processor;


use App\Form\Model\SoporteDto;
use App\Form\Type\SoporteFormType;
use App\Service\MailTool;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class SoporteFormProcessor
{
    private $descripcionDatosManager;
    private $formFactory;
    private $mailtool;
    public function __construct(FormFactoryInterface $formFactory,
                                MailTool $mailtool
    ) {
        $this->formFactory = $formFactory;
        $this->mailtool = $mailtool;
    }

    public function __invoke(Request $request): array
    {
        //creo el DTo
        $soporteDto = new SoporteDto(); 
        $form = $this->formFactory->create(SoporteFormType::class, $soporteDto);
        //recojo los datos en el dto
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'El fomulario no ha sido enviado'];
        }
        if ($form->isValid()) {   
            //envío el correo
            $this->mailtool->sendEmailSoporte($soporteDto);
            return [$soporteDto, null];   
        }
        return [null, $form];
    }
}
