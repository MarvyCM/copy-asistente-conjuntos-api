<?php
namespace App\Controller;

use App\Service\Processor\SoporteFormProcessor;
use App\Form\Model\SoporteDto;
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
 * Descripción: Es el controlador del envío mail a soporte
 */
class AyudaController extends AbstractFOSRestController
{

    /**
     * @Rest\Post(path="/api/v1/ayuda/soporte")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Correcto, correo enviado"
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
     *      summary="Envia un correo a soporte",
     *      tags={"Ayuda"},
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
     *              @SWG\Property(property="tipoPeticion", type="string", example="incidencia, consulta, mejora", description="Tipo de soporte solicitado"),
     *              @SWG\Property(property="titulo", type="string", example="Formatos de las base datos", description="Título del soporte requerido"),
     *              @SWG\Property(property="descripcion", type="string", example="Hola: me gustaría...", description="Descripción del soporte requerido"),
     *              @SWG\Property(property="nombre", type="string", example="Alfonso Martinez", description="Nombre del usuario que solcita el soporte"),
     *              @SWG\Property(property="emailContacto", type="string", example="yo@mihost.com", description="Email de respuesta del usuario"),
     *          ),
     *      )
     * )
     */
    public function postSoporteAction(
        SoporteFormProcessor $soporteFormProcessor,
        LoggerInterface $logger,
        Request $request) 
    {
        $errorProceso = "";
        $soporte = new SoporteDto();; 

        [$soporte, $error] = ($soporteFormProcessor)($request);
        if (!empty($error)) {
            return new JsonResponse($error, Response::HTTP_UNPROCESSABLE_ENTITY,['error_proceso'=>$error]);
        }
        $statusCode = $soporte ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data = $soporte ?? $error;
        return View::create($data, $statusCode);
    }

}