<?php

namespace App\Service\Processor;

use App\Entity\OrigenDatos;
use App\Form\Model\OrigenDatosDto;
use App\Form\Type\OrigenDatosDataBaseFormType;
use App\Service\Manager\OrigenDatosManager;
use App\Service\Manager\DescripcionDatosManager;
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
class OrigenDatosDataBaseFormProcessor
{

    private $descripcionDatosManager;
    private $origenDatosManager;
    private $fileUploader;
    private $formFactory;
    private $dataBaseTool;

    public function __construct(
        OrigenDatosManager $origenDatosManager,
        DescripcionDatosManager $descripcionDatosManager,
        FormFactoryInterface $formFactory,
        DataBaseTool $dataBaseTool)
    {
        $this->origenDatosManager = $origenDatosManager;
        $this->descripcionDatosManager = $descripcionDatosManager;
        $this->formFactory = $formFactory;
        $this->dataBaseTool = $dataBaseTool;
    }

    public function __invoke(OrigenDatos $origendatos, bool $isTest, Request $request): array
    {
        $origendatosDto = OrigenDatosDto::createFromOrigenDatos($origendatos);
        // creo el formulario vacío con los datos actuales
        $form = $this->formFactory->create(OrigenDatosDataBaseFormType::class, $origendatosDto);
        $form->handleRequest($request);
        $errorProceso ="";
        $campos = "";
        if (!$form->isSubmitted()) {  
            $errorProceso = 'Form is not submitted';
            return [null, $errorProceso, null];
        }
        if ($form->isValid()) {
            if ($origendatosDto->tipoOrigen == TipoOrigenDatosEnum::BASEDATOS) {
                $origendatos->setTipoOrigen($origendatosDto->tipoOrigen);
                $origendatos->setTipoBaseDatos($origendatosDto->tipoBaseDatos);
                $origendatos->setUsuario($origendatosDto->usuario);
                $origendatos->setSesion($origendatosDto->sesion);
                $origendatos->setHost($origendatosDto->host);
                $origendatos->setServicio($origendatosDto->servicio);
                $origendatos->setEsquema($origendatosDto->esquema);
                $origendatos->setPuerto($origendatosDto->puerto);
                $origendatos->setTabla($origendatosDto->tabla);
                $origendatos->setUsuarioDB($origendatosDto->usuarioDB);
                $origendatos->setContrasenaDB($origendatosDto->contrasenaDB);
                $origendatos->setData("");
                [$campos,$filas,$errorProceso] = $this->dataBaseTool->PruebaConexion($origendatosDto,1);
                //extraigo los campos para mostarselos al usuario
                if (isset($campos)){
                    $origendatos->setCampos($campos);
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
                        } else {
                            throw new \Exception("La descripción de datos con id: {$origendatosDto->idDescripcion} ya contiene un origen de datos", 1);
                            return [null, $form];
                        }
                    }
                    $origendatos->updatedTimestamps();
                    $this->origenDatosManager->save($origendatos);
                    $this->origenDatosManager->reload($origendatos);
                }
            }     
            return [$origendatos, $errorProceso, null]; 
        }
        return [null, $errorProceso, $form];
    }
} 