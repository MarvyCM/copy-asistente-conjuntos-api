<?php

namespace App\Service\Processor;

use App\Entity\User;
use App\Form\Model\UserDto;
use App\Form\Type\UserFormType;
use App\Service\Manager\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node\Expr\Isset_;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/*
 * Descripción: Clase que realiza el trabajo de validar y enviar los datos al repositorio corespondiente
 *              Controla la validacion del formulario y serializa el Dto a la clase entidad
 *              Guarda en Base de datos
 * 
*/

class UserFormProcessor
{
    private $userManager;
    private $formFactory;

    public function __construct(
        UserManager $userManager,
        FormFactoryInterface $formFactory
    ) {
        $this->userManager = $userManager;
        $this->formFactory = $formFactory;
    }

    public function __invoke(User $user, 
                             Request $request,
                             UserPasswordEncoderInterface $encoder): array
    {
        //creo el dto
        $userDto = UserDto::createFromUser($user);
        //creo el formulario en base al dto
        $form = $this->formFactory->create(UserFormType::class, $userDto);
        //recojo los datos del rest
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'Form is not submitted'];
        }
        if ($form->isValid()) {
            //compruebo que el usuario existe
            $usuarioExistente = $this->userManager->findByUserName($userDto->username);   
            if (!empty($usuarioExistente )){
                //actualizo solo el roll que el lo único que pude cambiar desde el funcional
                $user = $usuarioExistente[0];
                $roles = array($userDto->roles);
                $user->setRoles($roles);  
                $user->updatedTimestamps();  
            } else {
                //creo el usuario
                $user->setPlainPassword($userDto->password);
                $user->setPassword($encoder->encodePassword($user, $userDto->password));
                $user->setUsername($userDto->username);
                $user->setEmail($userDto->username);
                $roles = array($userDto->roles);
                $user->setRoles($roles);
                $user->setName("nombreusuario");
                $user->updatedTimestamps();
            }
            //guardo
            $this->userManager->save($user);
            $this->userManager->reload($user);
            return [$user, null];
        } 
        return [null, $form];
    }
}