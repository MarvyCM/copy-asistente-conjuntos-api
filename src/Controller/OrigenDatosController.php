<?php
namespace App\Controller;

use App\Service\Processor\OrigenDatosDataBaseFormProcessor;
use App\Service\Processor\OrigenDatosDataFormProcessor;
use App\Service\Processor\AlineacionDatosDataFormProcessor;
use App\Service\Processor\OrigenDatosFichaFormProcessor;
use App\Service\Manager\OrigenDatosManager;

use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;


/*
 * Descripción: Es el controlador de todas la llamadas del paso 2, donde se crean y actualizan 
 *              los orígenes de datos a un a descripcion.
 *              Los orígenes pueden ser de fichero, url o base datos en cualquier formato.
 */
class OrigenDatosController extends AbstractFOSRestController
{
   
    /**
     * @Rest\Get(path="/api/v1/origendatos/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen datos por id"
     * )
     * 
     * @SWG\Get(
     *      summary="Devuelve el decripción de datos por id",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *    )
     */
    public function getSingleAction(
        int $id,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger
    ) {
        $origenDatos = $origenDatosManager->find($id);
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        }
        return $origenDatos;
    }

     /**
     * @Rest\Get(path="/api/v1/origendatos/datosficha/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, la comprobación de los campos y el array con los datos"
     * )
     * 
     * @SWG\Get(
     *      summary="Devuelve la lista actual de los campos y el array de datos por id",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *    )
     */
    public function getDatosFichaAction(
        int $id,
        OrigenDatosFichaFormProcessor $origenDatosFichaFormProcessor,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger,
        Request $request
    ) {
        $errorProceso ="";
        $campos = "";
        $data = null;

        $origenDatos = $origenDatosManager->find($id);
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        } 
        [$campos, $data, $errorProceso] = $origenDatosFichaFormProcessor($origenDatos);
        if (!empty($errorProceso)) {
            return new JsonResponse($errorProceso,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProceso]);
        }
        $datosFicha = array("campos"=>$campos, "data"=>$data, "error_proceso"=>$errorProceso);
        $statusCode = $campos ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
        return View::create($datosFicha , $statusCode);
    }

    /**
     * @Rest\Post(path="/api/v1/origendatos/database")
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, decripción de datos creado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     *
     * @SWG\Post(
     *      summary="Inserta un origen de datos data base",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="BASEDATOS", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="tipoBaseDatos", type="string", example="MYSQL", description="El tipo base datos del origen de los datos"),
     *              @SWG\Property(property="host", type="string", example="localhost", description="El host del origen de los datos"),
     *              @SWG\Property(property="puerto", type="datetime", example="3306", description="El puerto de la base datos del origen de los datos"),
     *              @SWG\Property(property="servicio", type="datetime", example="", description="El servicio de la base datos del origen de los datos"),
     *              @SWG\Property(property="esquema", type="string", example="asistente_conjuntos", description="El esquema de la base datos  origen de los datos"),
     *              @SWG\Property(property="tabla", type="string", example="user", description="La tablao vista  de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuarioDB", type="string", example="root", description="El usuario de la base datos origen de los datos"),
     *              @SWG\Property(property="contrasenaDB", type="string", example="adminDP25@", description="La contraseña de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      )
     * )
     */
    public function postDataBaseAction(
        OrigenDatosManager $origenDatosManager,
        OrigenDatosDataBaseFormProcessor $descripcionDataBaseFormProcessor,
        LoggerInterface $logger,
        Request $request) 
    {
        $errorProceso = "";
        $origenDatos = $origenDatosManager->create();
        $errorProceso = "";
        $isTest = false;
        [$origenDatos,$errorProceso, $error] = ($descripcionDataBaseFormProcessor)($origenDatos, $isTest, $request);
        if (!empty($errorProceso)) {
            return new JsonResponse($errorProceso,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProceso]);
        }
        $statusCode = $origenDatos ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $origenDatos ?? $error;
        return View::create($data, $statusCode);
    }


    /**
     * @Rest\Post(path="/api/v1/origendatos/database/test")
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, decripción de datos creado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     *
     * @SWG\Post(
     *      summary="Prueba sin insertar un origen de datos data base",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="BASEDATOS", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="tipoBaseDatos", type="string", example="MYSQL", description="El tipo base datos del origen de los datos"),
     *              @SWG\Property(property="host", type="string", example="localhost", description="El host del origen de los datos"),
     *              @SWG\Property(property="puerto", type="datetime", example="3306", description="El puerto de la base datos del origen de los datos"),
     *              @SWG\Property(property="servicio", type="datetime", example="", description="El servicio de la base datos del origen de los datos"),
     *              @SWG\Property(property="esquema", type="string", example="asistente_conjuntos", description="El esquema de la base datos  origen de los datos"),
     *              @SWG\Property(property="tabla", type="string", example="user", description="La tablao vista  de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuarioDB", type="string", example="root", description="El usuario de la base datos origen de los datos"),
     *              @SWG\Property(property="contrasenaDB", type="string", example="adminDP25@", description="La contraseña de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      )
     * )
     */
    public function testDataBaseAction(
        OrigenDatosManager $origenDatosManager,
        OrigenDatosDataBaseFormProcessor $descripcionDataBaseFormProcessor,
        LoggerInterface $logger,
        Request $request) 
    {
        $errorProceso = "";
        $origenDatos = $origenDatosManager->create();
        $errorProceso = "";
        $isTest = true;
        [$origenDatos,$errorProceso, $error] = ($descripcionDataBaseFormProcessor)($origenDatos, $isTest, $request);
        if (!empty($errorProceso)) {
            return new JsonResponse($errorProceso,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProceso]);
        }
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $origenDatos;
        return View::create($data, $statusCode);
    }

    /**
     * @Rest\Post(path="/api/v1/origendatos/data")
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos creado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     *
     * @SWG\Post(
     *      summary="Inserta un origen de datos de un archivo o url",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="ARCHIVO", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="data", type="string", example="archivo en base64", description="El origen del origen de los datos pude ser una url o archivo en base 64"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      )
     * )
     */
    public function postDataAction(
        OrigenDatosManager $origenDatosManager,
        OrigenDatosDataFormProcessor $origenDatosFormProcessor,
        LoggerInterface $logger,
        Request $request) 
    {
        $origenDatos = $origenDatosManager->create();
        $isTest = false;
        [$origenDatos, $error, $errorProces] = ($origenDatosFormProcessor)($origenDatos, $isTest , $request);
        if (!empty($errorProces)) {
            return new JsonResponse($errorProces,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProces]);
        }
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;

        $data = $origenDatos;
        return View::create($data, $statusCode);
    }

    /**
     * @Rest\Post(path="/api/v1/origendatos/data/test")
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos creado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     *
     * @SWG\Post(
     *      summary="Prueba sin insertar un origen de datos de un archivo o url",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="ARCHIVO", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="data", type="string", example="archivo en base64", description="El origen del origen de los datos pude ser una url o archivo en base 64"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      )
     * )
     */
    public function testDataAction(
        OrigenDatosManager $origenDatosManager,
        OrigenDatosDataFormProcessor $origenDatosFormProcessor,
        LoggerInterface $logger,
        Request $request) 
     {
        $origenDatos = $origenDatosManager->create();
        $isTest = true;
        [$origenDatos, $error, $errorProces] = ($origenDatosFormProcessor)($origenDatos,  $isTest, $request);
        if (!empty($errorProces)) {
            return new JsonResponse($errorProces,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProces]);
        }
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;

        $data = $origenDatos;
        return View::create($data, $statusCode);
    }

     /**
     * @Rest\Post(path="/api/v1/origendatos/database/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos modificado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     * 
     * @SWG\Post(
     *      summary="Edita un origen de datos tipo base de datos",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="BASEDATOS", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="tipoBaseDatos", type="string", example="MYSQL", description="El tipo base datos del origen de los datos"),
     *              @SWG\Property(property="host", type="string", example="localhost", description="El host del origen de los datos"),
     *              @SWG\Property(property="puerto", type="datetime", example="3306", description="El puerto de la base datos del origen de los datos"),
     *              @SWG\Property(property="servicio", type="datetime", example="_", description="El servicio de la base datos del origen de los datos"),
     *              @SWG\Property(property="esquema", type="string", example="asistente_conjuntos", description="El esquema de la base datos  origen de los datos"),
     *              @SWG\Property(property="tabla", type="string", example="user", description="La tablao vista  de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuarioDB", type="string", example="root", description="El usuario de la base datos origen de los datos"),
     *              @SWG\Property(property="contrasenaDB", type="string", example="adminDP25@", description="La contraseña de la base datos  del origen de los datos"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      ),
     * )
     */
    public function editDataBaseAction(
        int $id,
        OrigenDatosDataBaseFormProcessor $origenDatosDataBaseFormProcessor,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger,
        Request $request) 
    {
        $origenDatos = $origenDatosManager->find($id);
        $isTest = false;
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        }
        [$origenDatos, $errorProceso, $error] = ($origenDatosDataBaseFormProcessor)($origenDatos, $isTest,$request);
        if (!empty($errorProceso)) {
            return new JsonResponse($errorProceso,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProceso]);
        }
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST;
        $data = $origenDatos;
        return View::create($data, $statusCode);
    }


     /**
     * @Rest\Post(path="/api/v1/origendatos/data/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos modificado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     * 
     * @SWG\Post(
     *      summary="Edita un origen de datos tipo archivo o url",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idDescripcion", type="string", example="1", description="El id de la decripción de datos"),
     *              @SWG\Property(property="tipoOrigen", type="string", example="ARCHIVO", description="El tipo Origen del origen de los datos"),
     *              @SWG\Property(property="tipoBaseDatos", type="string", example="MYSQL", description="El tipo base datos del origen de los datos"),
     *              @SWG\Property(property="data", type="string", example="archivo en base64", description="El origen del origen de los datos pude ser una url o archivo en base 64"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      ),
     * )
     */
    public function editDataAction(
        int $id,
        OrigenDatosDataFormProcessor $origenDatosDataFormProcessor,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger,
        Request $request) 
    {
        $origenDatos = $origenDatosManager->find($id);
        $isTest = false;
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        }
        [$origenDatos, $errorProceso, $error] = ($origenDatosDataFormProcessor)($origenDatos, $isTest, $request);
        if (!empty($errorProceso)) {
            return new JsonResponse($errorProceso,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$errorProceso]);
        }
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST;
        $data = $origenDatos;
        return View::create($data, $statusCode);
    }


     /**
     * @Rest\Post(path="/api/v1/origendatos/alineacion/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos modificado"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="Error de sistema"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     * 
     * @SWG\Post(
     *      summary="Edita un origen de datos insertando la alineación EI2A y la ralación con sus campos",
     *      tags={"AlineacionDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="JSON de registro",
     *          type="json",
     *          format="application/json",
     *          @SWG\Schema(
     *              @SWG\Property(property="alineacionEntidad", type="string", example="http://purl.oclc.org/NET/ssnx/ssn#Accuracy", description="El la entidad seleccionada para alinear"),
     *              @SWG\Property(property="alineacionRelaciones", type="string", example="Una strig en base 64", description="Es la relaccion de las entidades y los campos del origen de los datos en formato JSON en base64"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario del origen de los datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario del origen de los datos"),
     *          ),
     *      ),
     * )
     */
    public function editAlineacionAction(
        int $id,
        AlineacionDatosDataFormProcessor $alineacionDatosDataFormProcessor,
        OrigenDatosManager $alineacionDatosManager,
        LoggerInterface $logger,
        Request $request) 
    {
        $errorProceso = "";
        $origenDatos = $alineacionDatosManager->find($id);
        $isTest = false;
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        }
        [$origenDatos, $error] = ($alineacionDatosDataFormProcessor)($origenDatos, $request);
        if (!empty($error)) {
            return new JsonResponse($error,Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $origenDatos ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST;
        $data = $origenDatos;
        return View::create($data, $statusCode);
    }

    
    /**
     * @Rest\Delete(path="/api/v1/origendatos/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, origen de datos borrado"
     * )
     * @SWG\Delete(
     *      summary="Borra una descripción de datos",
     *      tags={"OrigenDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"}
     * )
     */
    public function deleteAction(
        int $id,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger
    ) {
        $origenDatos = $origenDatosManager->find($id);
        if (!$origenDatos) {
            return View::create('OrigenDatos not found', Response::HTTP_BAD_REQUEST);
        }
        $origenDatosManager->delete($origenDatos);
        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}