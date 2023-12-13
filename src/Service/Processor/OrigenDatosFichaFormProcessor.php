<?php

namespace App\Service\Processor;

use App\Entity\OrigenDatos;
use App\Form\Model\OrigenDatosDto;
use App\Form\Type\OrigenDatosDataFormType;
use App\Service\Manager\OrigenDatosManager;
use App\Service\DataFileTool;
use App\Service\DataBaseTool;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
 
/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class OrigenDatosFichaFormProcessor
{

    private $origenDatosManager;
    private $formFactory;
    private $dataFileTool;
    private $dataBaseTool;
    
    public function __construct(

        OrigenDatosManager $origenDatosManager,
        FormFactoryInterface $formFactory,
        DataFileTool $dataFileTool,
        DataBaseTool $dataBaseTool)
    {
        $this->origenDatosManager = $origenDatosManager;
        $this->formFactory = $formFactory;
        $this->dataFileTool = $dataFileTool;
        $this->dataBaseTool = $dataBaseTool;
    }

    public function __invoke(OrigenDatos $origendatos): array
    {
        $errorProceso ="";
        $campos = "";
        $data = null;
        // es una operacion de lectura donde se devuelve los campos y la muestra de datos
        switch ($origendatos->getTipoOrigen()) {
            case TipoOrigenDatosEnum::ARCHIVO:  
                    [$campos, $data ,$errorProceso] = $this->dataFileTool->DatosFichaIntegridadDatos($origendatos);  
                break;
            case TipoOrigenDatosEnum::URL:
                    [$campos, $data , $errorProceso]= $this->dataFileTool->DatosFichaIntegridadDatos($origendatos);
                break;
            case TipoOrigenDatosEnum::BASEDATOS:
                [$campos, $data , $errorProceso]= $this->dataBaseTool->DatosFichaIntegridadDatos($origendatos);
            break;
        } 
        
        return [$campos, $data, $errorProceso];       
    }
        
} 