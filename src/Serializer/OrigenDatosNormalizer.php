<?php

namespace App\Serializer;
use App\Enum\TipoOrigenDatosEnum;
use App\Entity\OrigenDatos;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/*
 * Descripción: Es la clase de symfony permite capturar y modificar modificar la salida http
 *                  
 */

class OrigenDatosNormalizer implements ContextAwareNormalizerInterface
{

    private $normalizer;
    private $urlHelper;

    public function __construct(
        ObjectNormalizer $normalizer,
        UrlHelper $urlHelper
    ) {
        $this->normalizer = $normalizer;
        $this->urlHelper = $urlHelper;
    }
    
    /*
    * Descripción: En esta caso estos añadiendo la ruta relativa del archivo cuando el origen datos es un archivo
                   Esto nos permite flexibilidad de los que respondemos, independientemente del ORM                  
    */
    public function normalize($origendatos, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($origendatos, $format, $context);

        if (!empty($origendatos->getData()) && $origendatos->getTipoOrigen()==TipoOrigenDatosEnum::ARCHIVO) { 
            $data['origendatos'] = $this->urlHelper->getAbsoluteUrl('/storage/default/' . $origendatos->getData());
        }
        //$data['nuevocampo'] = "nuevocampo";
        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof OrigenDatos;
    }
}