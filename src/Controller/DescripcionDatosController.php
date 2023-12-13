<?php
namespace App\Controller;

use App\Service\Processor\DescripcionDatosFormProcessor;
use App\Service\Processor\DescripcionDatosWorkFlowFormProcessor;
use App\Service\Manager\DescripcionDatosManager;
use App\Service\Manager\OrigenDatosManager;
use FOS\RestBundle\Controller\AbstractFOSRestController; 
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;
/*
 * Descripción: Es el controlador de todas la llamadas del paso 1 (1.1, 1.2 y 1.3)
 *              para crear o actualizar la descripción de los datos.
 *              También controla la ficha del conjunto de datos y el listado.
 */
class DescripcionDatosController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/api/v1/descripciondatos", methods={"GET"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     *
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, listado de decripción datos par pagina y tamaño de la misma"
     * )
     * @SWG\Get(
     *      summary="Lista la descripcion de los datos paginando por numero pagina y tamaño pagina",
     *      tags={"DescripcionDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     * )
     * @SWG\Parameter(
     *          name="numeropagina",
     *          in="query",
     *          type="integer",
     *          description="Numero de pagina"),
     * )
     * @SWG\Parameter(
     *          name="tamanopagina",
     *          in="query",
     *          type="integer",
     *          description="Tamaño de la pagina"),
     * )
     */
    public function getAction(
        DescripcionDatosManager $descripcionDatosManager,
        LoggerInterface $logger
    )  {
        $numeropagina = $_GET["numeropagina"];
        $tamanopagina = $_GET["tamanopagina"];
        $usuario = $this->getCurrentUser();
        $username =  $usuario->getUsername();
        if (in_array('ROLE_ADMIN',$usuario->getRoles())){
            $username = "ROLE_ADMIN";
        }
        return $descripcionDatosManager->getRepository()->getDataUsersByPage($numeropagina, $tamanopagina, $username);
    }

    /**
     * @Rest\Get(path="/api/v1/descripciondatos/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, descripcion datos por id"
     * )
     * 
     * @SWG\Get(
     *      summary="Devuelve el decripción de datos por id",
     *      tags={"DescripcionDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"},
     *    )
     */
    public function getSingleAction(
        int $id,
        DescripcionDatosManager $descripcionDatosManager,
        LoggerInterface $logger
    ) {
        $descripcionDatos = $descripcionDatosManager->find($id);
        if (!$descripcionDatos) {
            return View::create('DescripcionDatos not found', Response::HTTP_BAD_REQUEST);
        }
        return $descripcionDatos;
    }

    /**
     * @Rest\Post(path="/api/v1/descripciondatos")
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
     *      summary="Inserta una descripción de datos",
     *      tags={"DescripcionDatos"},
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
     *              @SWG\Property(property="identificacion", type="string", example="1dentificacion", description="La identificación de la decripción de datos"),
     *              @SWG\Property(property="denominacion", type="string", example="denominacion", description="La denominacion de la decripción de datos"),
     *              @SWG\Property(property="descripcion", type="string", example="descripcion", description="La descripcion la decripción de datos"),
     *              @SWG\Property(property="frecuenciaActulizacion", type="string", example="Anual", description="La frecuencia de actualización de la decripción de datos"),
     *              @SWG\Property(property="fechaInicio", type="datetime", example="2021-01-19 00:00", description="La fecha inicio de la decripción de datos"),
     *              @SWG\Property(property="fechaFin", type="datetime", example="2021-01-31 00:00", description="La fecha inicio de la decripción de datos"),
     *              @SWG\Property(property="territorio", type="string", example="Aragon", description="El territorio de la decripción de datos"),
     *              @SWG\Property(property="instancias", type="string", example="instancias", description="Las instancias de la decripción de datos"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario de la decripción de datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario de la decripción de datos"),
     *              @SWG\Property(property="estado", type="string", example="BORRADOR", description="Estado de la decripción de datos"),
     *              @SWG\Property(property="estadoAlta", type="string", example="1.1 descripcion", description="Estado de alta la decripción de datos"),
     *          ),
     *      )
     * )
     */
    public function postAction(
        DescripcionDatosManager $descripcionDatosManager,
        DescripcionDatosFormProcessor $descripcionDatosFormProcessor,
        LoggerInterface $logger,
        Request $request
    ) {
        $descripcionDatos = $descripcionDatosManager->create();
        [$descripcionDatos, $error] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        $statusCode = $descripcionDatos ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $descripcionDatos ?? $error;
        return View::create($data, $statusCode);
    }

     /**
     * @Rest\Post(path="/api/v1/descripciondatos/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, decripción de datos modificado"
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
     *      summary="Edita una descripción de datos",
     *      tags={"DescripcionDatos"},
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
     *              @SWG\Property(property="identificacion", type="string", example="1dentificación", description="La identificación de la decripción de datos"),
     *              @SWG\Property(property="denominacion", type="string", example="denominacion", description="La denominacion de la decripción de datos"),
     *              @SWG\Property(property="descripcion", type="string", example="descripcion", description="La descripcion la decripción de datos"),
     *              @SWG\Property(property="frecuenciaActulizacion", type="string", example="Anual", description="La frecuencia de actualización de la decripción de datos"),
     *              @SWG\Property(property="fechaInicio", type="datetime", example="2021-01-19 00:00:00", description="La fecha inicio de la decripción de datos"),
     *              @SWG\Property(property="fechaFin", type="datetime", example="2021-01-31 00:00:00", description="La fecha inicio de la decripción de datos"),
     *              @SWG\Property(property="territorio", type="string", example="Aragon", description="El territorio de la decripción de datos"),
     *              @SWG\Property(property="instancias", type="string", example="instancias", description="Las instancias de la decripción de datos"),
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario de la decripción de datos"),
     *              @SWG\Property(property="organoResponsable", type="string", example="Ayuntamiento", description="El órgano responsable de la decripción de datos"),
     *              @SWG\Property(property="finalidad", type="string", example="La finalidad es", description="La finalidad de la decripción de datos"),
     *              @SWG\Property(property="condiciones", type="string", example="Real decreto de proteccion de datos", description="Las condiciones de la decripción de datos"),
     *              @SWG\Property(property="vocabularios", type="string", example="rojo;azul;verde", description="Los vocabularios de la decripción de datos"),
     *              @SWG\Property(property="servicios", type="string", example="servicio1;servicio2", description="Los servicios de la decripción de datos"),
     *      *       @SWG\Property(property="etiquetas", type="string", example="etiqueta1;etiqueta2", description="Las etiquetas de la decripción de datos"),
     *              @SWG\Property(property="estructura", type="string", example="estructura", description="La  estructura de la decripción de datos"),
     *              @SWG\Property(property="estructuraDenominacion", type="string", example="estructura Denominacion", description="La denominacion estructura de la decripción de datos"),
     *              @SWG\Property(property="formatos", type="string", example="xml;json", description="Los formatos de la decripción de datos"),
     *              @SWG\Property(property="estado", type="string", example="EN_ESPERA", description="El estado de la decripción de datos"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario de la decripción de datos"),
     *          ),
     *      ),
     * )
     */
    public function editAction(
        int $id,
        DescripcionDatosFormProcessor $descripcionDatosFormProcessor,
        DescripcionDatosManager $descripcionDatosManager,
        LoggerInterface $logger,
        Request $request
    ) {
        $descripcionDatos = $descripcionDatosManager->find($id);
        if (!$descripcionDatos) {
            return View::create('DescripcionDatos not found', Response::HTTP_BAD_REQUEST);
        }
        [$descripcionDatos, $error] = ($descripcionDatosFormProcessor)($descripcionDatos, $request);
        $statusCode = $descripcionDatos ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST;
        $data = $descripcionDatos ?? $error;
        return View::create($data, $statusCode);
    }


     /**
     * @Rest\Post(path="/api/v1/descripciondatos/workflow/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, decripción de datos modificado"
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
     *      summary="Edita el estado de una una descripción de datos en su workflow",
     *      tags={"DescripcionDatos"},
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
     *              @SWG\Property(property="usuario", type="string", example="sitomarmo@aragopedi.com", description="El usuario propietario de la decripción de datos"),
     *              @SWG\Property(property="estado", type="string", example="EN_ESPERA", description="El estado de la decripción de datos"),
     *              @SWG\Property(property="descripcion", type="string", example="Descripcion del administrador ", description="Indicaciones del administrador acerca del cambio de estado"),
     *              @SWG\Property(property="sesion", type="string", example="121342456", description="La sesion del usuario de la decripción de datos"),
     *          ),
     *      ),
     * )
     */
    public function editEstadoAction(
        int $id,
        DescripcionDatosWorkFlowFormProcessor $descripcionDatosWorkFlowFormProcessor,
        DescripcionDatosManager $descripcionDatosManager,
        LoggerInterface $logger,
        Request $request
    ) {
        $descripcionDatos = $descripcionDatosManager->find($id);
        if (!$descripcionDatos) {
            return View::create('DescripcionDatos not found', Response::HTTP_BAD_REQUEST);
        }
        [$descripcionDatos, $error] = ($descripcionDatosWorkFlowFormProcessor)($descripcionDatos, $request);
        $statusCode = $descripcionDatos ? Response::HTTP_ACCEPTED: Response::HTTP_BAD_REQUEST;
        $data = $descripcionDatos ?? $error;
        return View::create($data, $statusCode);
    }


    /**
     * @Rest\Delete(path="/api/v1/descripciondatos/{id}", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"descripcionDatos"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, decripción de datos borrado"
     * )
     * @SWG\Delete(
     *      summary="Borra una descripción de datos",
     *      tags={"DescripcionDatos"},
     *      consumes={"application/json"},
     *      produces={"application/json"}
     * )
     */
    public function deleteAction(
        int $id,
        DescripcionDatosManager $descripcionDatosManager,
        OrigenDatosManager $origenDatosManager,
        LoggerInterface $logger
    ) {
        $descripcionDatos = $descripcionDatosManager->find($id);
        if (!$descripcionDatos) {
            return View::create('DescripcionDatos not found', Response::HTTP_BAD_REQUEST);
        }
        $origen = $descripcionDatos->getOrigenDatos();
        if (!empty($origen)){
            $origenDatosManager->delete($origen);
        }
        $descripcionDatosManager->delete($descripcionDatos);
        return View::create(null, Response::HTTP_NO_CONTENT);
    }


    /*
    Devuelve el usuario actual de JWT: anonimo o autenticado.
    */
    protected function getCurrentUser()
    {

        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The Security Bundle is not registered in your application.');
        }
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }
        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }
        return $user;
    }
}