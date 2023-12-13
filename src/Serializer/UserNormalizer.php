<?php

namespace App\Serializer;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/*
 * Descripción: Es la clase de symfony permite capturar y modificar modificar la salida http
 *                  
 */
class UserNormalizer implements ContextAwareNormalizerInterface
{

    private $normalizer;
    private $urlHelper;
    private $JWTManager;
    
    public function __construct(
        ObjectNormalizer $normalizer,
        UrlHelper $urlHelper,
        JWTTokenManagerInterface $JWTManager
    ) {
        $this->normalizer = $normalizer;
        $this->urlHelper = $urlHelper;
        $this->JWTManager = $JWTManager;
    }

     /*
    * Descripción: En esta caso estos añadiendo el token JWT en al llamada registro
    +              Esto nos permite flexibilidad de los que respondemos, independientemente del ORM
    */             
    public function normalize($user, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($user, $format, $context);

        if (!empty($user->getUsername())) { 
            $data['token'] = $this->getTokenUser($user);
        }
        //$data['nuevocampo'] = "nuevocampo";
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof User;
    }

    public function getTokenUser($user)
    {
        return $this->JWTManager->create($user);
    }
    
}