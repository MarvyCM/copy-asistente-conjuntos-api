<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonEncode;

/*
    * Descripción: Algunos campos se pasan en base64 esta funcion a modo de helper ayuda a descodificarlos
*/
class Base64JsonTool
{

    private $defaultStorage;
    /*
     * Descripción: Decodifica un campo pasado en base64
     * 
     * Parametros: 
     *          base64field: campo en base64
    */
    public static function getJosonBase64(?string $base64field): string
    {
        $jsonFiled = "";
        if (!empty($base64field)){
            $jsonFiled = base64_decode($base64field);
        }
        return $jsonFiled;
    }
}