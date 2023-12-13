<?php

namespace App\Form\Model;
/*
 * Descripción: Es la clase dto del soporte de datos del conjunto de datos. 
 *              El objeto que serializa los datos del la llamada JSON             
 */
class SoporteDto {
    public $tipoPeticion;
    public $titulo;
    public $descripcion;
    public $nombre;
    public $emailContacto;

    public function __construct()
    {

    }
}
