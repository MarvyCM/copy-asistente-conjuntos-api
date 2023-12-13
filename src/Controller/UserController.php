<?php
/**
 * ApiController.php
 *
 * API Controller
 *
 * @category   Controller
 * @package    MyKanban
 * @author     Francisco Ugalde
 * @copyright  2018 www.franciscougalde.com
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */

namespace App\Controller;

use App\Service\Processor\UserFormProcessor;
use App\Service\Manager\UserManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

use Psr\Log\LoggerInterface;

/*
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Form\Model\UserDto;
use App\Form\Type\UserFormType;
*/


/*
 * Descripción: Es el controlador del componente de seguridad JWT
*/
/**
 * Class ApiController
 *
 * @Route("/api")
 */
class UserController extends AbstractFOSRestController
{

    // USER URI's

    /**
     * @Rest\Post(path="/login_check")
     * @Rest\View(serializerGroups={"user"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="User was logged in successfully"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="User was not logged in successfully"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     * 
     * @SWG\Post(
     *      summary="Devuelve el token JWT de un usuario en el sistema",
     *      tags={"User"},
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
     *              @SWG\Property(property="_username", type="string", example="usuario@dominio.com", description="El usaurio"),
     *              @SWG\Property(property="_password", type="string", example="123456", description="La contraseña"), 
     *          ),
     *      ),
     * )
     */
    public function getLoginCheckAction() {

    }


     /**
     * @Rest\Post(path="/register")
     * @Rest\View(serializerGroups={"user"}, serializerEnableMaxDepthChecks=true)
     * 
     * @SWG\Response(
     *     response=200,
     *     description="User was successfully registered"
     * )
     * @SWG\Response(
     *     response=500,
     *     description="User was not registered in successfully"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Error en los datos enviados"
     * )
     * 
     * @SWG\Post(
     *      summary="Registra un usuario de un usuario en el sistema",
     *      tags={"User"},
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
     *              @SWG\Property(property="username", type="string", example="usuario@dominio.com", description="El nombre el usuario"),
     *              @SWG\Property(property="password", type="string", example="123456", description="La contraseña"), 
     *              @SWG\Property(property="roles", type="string", example="ROLE_USER", description="La el tokenldap del usuario"), 
     *          ),
     *      ),
     * )
     */
    public function registerAction(        
        UserManager $userManager,
        UserFormProcessor $userFormProcessor,
        Request $request,
        UserPasswordEncoderInterface $encoder,
        ContainerBagInterface $params,
        LoggerInterface $logger) {
    
            $user = $userManager->Create();
            [$user, $error] = ($userFormProcessor)($user, $request, $encoder);
            $statusCode = $user ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
            $data = $user ?? $error;
            $logger->info($user->getUsername());
            return View::Create($data, $statusCode);
    }

}