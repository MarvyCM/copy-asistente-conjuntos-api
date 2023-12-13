<?php

namespace App\Service\Processor;

use App\Entity\OrigenDatos;
use App\Form\Model\OrigenDatosDto;
use App\Form\Type\OrigenDatosDataFormType;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\DataFileTool;
use App\Enum\TipoOrigenDatosEnum;
use App\Enum\EstadoAltaDatosEnum;
use App\Service\FileUploader;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

 /*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/
class OrigenDatosDataFormProcessor
{

    private $origenDatosManager;
    private $descripcionDatosManager;
    private $fileUploader;
    private $formFactory;
    private $dataFileTool;

    public function __construct(

        OrigenDatosManager $origenDatosManager,
        DescripcionDatosManager $descripcionDatosManager,
        FileUploader $fileUploader,
        FormFactoryInterface $formFactory,
        DataFileTool $dataFileTool)
    {
        $this->origenDatosManager = $origenDatosManager;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->fileUploader = $fileUploader;
        $this->formFactory = $formFactory;
        $this->dataFileTool = $dataFileTool;
    }

    public function __invoke(OrigenDatos $origendatos, bool $isTest, Request $request): array
    {
        $origendatosDto = OrigenDatosDto::createFromOrigenDatos($origendatos);
          // creo el formulario vacío con los datos actuales
        $form = $this->formFactory->create(OrigenDatosDataFormType::class, $origendatosDto);
        $form->handleRequest($request);
        $errorProceso ="";
        if (!$form->isSubmitted()) {
            $errorProceso = 'Form is not submitted';
            return [null, $errorProceso, null];
        }
        if ($form->isValid()) {
            //el formulario pude venir de file o de url
            $origendatos->setUsuario($origendatosDto->usuario);
            $origendatos->setSesion($origendatosDto->sesion);
            $origendatos->setTipoOrigen($origendatosDto->tipoOrigen);

            $origendatos->setTipoBaseDatos("");
            $origendatos->setHost("");
            $origendatos->setServicio("");
            $origendatos->setEsquema("");
            $origendatos->setPuerto("");
            $origendatos->setTabla("");
            $origendatos->setUsuarioDB("");
            $origendatos->setContrasenaDB("");
            //ahora dependiendo del que venga de url o de file
            switch ($origendatosDto->tipoOrigen) {
                case TipoOrigenDatosEnum::ARCHIVO:
                    if (!isset($origendatosDto->origenDatos)) {
                        $filename = $this->fileUploader->uploadBase64File($origendatosDto->data);
                        $origendatosDto->data = $filename;
                        $origendatos->setData($filename);
                        //el componente es el mismo para url y file
                        [$campos,$errorProceso] = $this->dataFileTool->PruebaIntegridadDatos($origendatosDto);  
                        if (isset($campos)){
                            $origendatos->setCampos($campos);
                        }  
     
                    }
                    break;
                case TipoOrigenDatosEnum::URL:
                    if (!isset($origendatosDto->origenDatos)) {
                        $origendatos->setData($origendatosDto->data);
                        //el componente es el mismo para url y file
                        [$campos,$errorProceso]= $this->dataFileTool->PruebaIntegridadDatos($origendatosDto);
                        if (isset($campos)){
                            $origendatos->setCampos($campos);
                        }
        
                    }
                    break;
            } 
             //si no es test gurado
            if (isset($campos) && !$isTest){ 
                $descripciondatos = $this->descripcionDatosManager->find($origendatosDto->idDescripcion);
                if ($descripciondatos->getEstadoAlta()== EstadoAltaDatosEnum::origen_url) {
                    $descripciondatos->setEstadoAlta(EstadoAltaDatosEnum::alineacion);
                } 
                $descripciondatos->updatedTimestamps();
                $descripciondatos = $this->descripcionDatosManager->save($descripciondatos);
                 //la relación es 1 a muchos
                if (empty($origendatos->getId())) {
                    if (empty($descripciondatos->getOrigenDatos())) {
                        $origendatos->setDescripcionDatos($descripciondatos);
                        $origendatos->setTipoBaseDatos("_");
                    } else {
                        throw new \Exception("La descripción de datos con id: {$origendatosDto->idDescripcion} ya contiene un origen de datos", 1);
                        return [null, $form];
                    }
                }
                $origendatos->updatedTimestamps();
                $this->origenDatosManager->save($origendatos);
                $origendatos = $this->origenDatosManager->reload($origendatos);
            }
            return [$origendatos, $errorProceso, null];       
        }
        return [null, $errorProceso, $form];
    }
} 