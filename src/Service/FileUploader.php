<?php

namespace App\Service;

use League\Flysystem\FilesystemOperator;

/*
 * Descripción: Es la utilidad que recibe el archivo en base64 y lo guarda en el servidor
*/
class FileUploader
{

    private $defaultStorage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->defaultStorage = $defaultStorage;
    }

    /*
     * Descripción: funcion principal
     * 
     * Parametros: 
     *          base64File: el archivo en base64 
     */
    public function uploadBase64File(string $base64File): string
    {
        //estraemos la extension
        $extension = explode('/', mime_content_type($base64File))[1];
        //estraemos el contenido
        $data = explode(',', $base64File);
        $extension = explode(';',explode('/',$data[0])[1])[0];
        $filename = sprintf('%s.%s', uniqid('data_', true), $extension);
        //guardamos el archivo
        if (!$this->defaultStorage->fileExists($filename)){
             $this->defaultStorage->write($filename, base64_decode($data[1]));
        }
        return $filename;
    }
}