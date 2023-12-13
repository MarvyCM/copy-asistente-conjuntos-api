<?php

namespace App\Service;

use App\Entity\OrigenDatos;
use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoExtensionEnum;
use App\Enum\TipoOrigenDatosEnum;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use League\Flysystem\FilesystemOperator;

/*
 * Descripción: Es la utilidad que recibe el origen de datos el formato archivo o url
 *              En caso de ser url se descarga el contenido
 *              En caso de ser archivo lo gurada y lo trata como url (url local)
 *              Para los 4 tipos de formato (json, xml csv xls) 
*/

class DataFileTool
{

    private $origenDatosDto;
    private $projectDir;
    private $defaultStorage;

    public function __construct(string $projectDir,
                                FilesystemOperator $defaultStorage)
    {
        $this->projectDir = $projectDir;
        $this->defaultStorage = $defaultStorage;
    }

     /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              Esta llamada devuelve el contenido del origen de datos para la ficha
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     */
    public function DatosFichaIntegridadDatos(OrigenDatos $origenDatos): array
    {
        $errorProceso ="";
        $campos = "";
        $data = null;
        $extension = strtoupper(pathinfo($origenDatos->getData())['extension']);
        $file = "";
        $condata  = true;
        //dependiendo de la extension y si es un tipo url o archivo realoza una llamada u otra
        switch ($extension) {
            case TipoExtensionEnum::XML:               
                if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $data, $errorProceso] = $this->TrataFileXML($origenDatos->getData(),$condata );
                } else if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::URL){
                    [$campos, $data, $errorProceso]= $this->TrataURLXML($origenDatos->getData(),$condata );   
                } 
            break;
            case TipoExtensionEnum::CSV: 
                if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $data, $errorProceso]= $this->TrataFileExcel($origenDatos->getData(),TipoExtensionEnum::CSV,$condata );
                } else if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::URL){
                    [$campos, $data, $errorProceso]= $this->TrataUrlExcel($origenDatos->getData(),TipoExtensionEnum::CSV,$condata );
                }
            break;
            case TipoExtensionEnum::JSON:  
                if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $data, $errorProceso] = $this->TrataFileJSON($origenDatos->getData(),$condata );
                } else if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::URL){
                    [$campos, $data, $errorProceso] = $this->TrataUrlJSON($origenDatos->getData(),$condata );  
                }        
            break;
            case TipoExtensionEnum::XLS:
                if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $data, $errorProceso] = $this->TrataFileExcel($origenDatos->getData(),TipoExtensionEnum::XLS,$condata );
                } else if ($origenDatos->getTipoOrigen() == TipoOrigenDatosEnum::URL){
                    [$campos, $data, $errorProceso] = $this->TrataUrlExcel($origenDatos->getData(),TipoExtensionEnum::XLS,$condata);
                }      
            break;
        }
        //quitamos ultimo ;
        $campos = (substr($campos, -1) == ";") ? substr($campos, 0, -1) : $campos;
        // devolvemos la lista de campos , las 100 primertas filas y/o el error si lo ha habido
        return [$campos, $data, $errorProceso];    
    }

     /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              Esta llamada no devuelve el contenido del origen de datos para la ficha
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen delos datos
     */
    public function PruebaIntegridadDatos(OrigenDatosDto $origenDatosDto): array
    {
        $campos = "";
        $errorProceso = "";
        $condata  = false;
        $extension = strtoupper(pathinfo($origenDatosDto->data)['extension']);
         //dependiendo de la extension y si es un tipo url o archivo realoza una llamada u otra
        switch ($extension) {
            case TipoExtensionEnum::XML:               
                if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $errorProceso] = $this->TrataFileXML($origenDatosDto->data,$condata);
                } else if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL){
                    [$campos, $errorProceso]= $this->TrataURLXML($origenDatosDto->data, $condata);       
                } 
            break;
            case TipoExtensionEnum::CSV: 
                if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $errorProceso]= $this->TrataFileExcel($origenDatosDto->data,TipoExtensionEnum::CSV,$condata);
                } else if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL){
                    [$campos, $errorProceso]= $this->TrataUrlExcel($origenDatosDto->data,TipoExtensionEnum::CSV, $condata);
                }
                
            break;
            case TipoExtensionEnum::JSON:  
                if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $errorProceso] = $this->TrataFileJSON($origenDatosDto->data,$condata);
                } else if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL){
                    [$campos, $errorProceso] = $this->TrataUrlJSON($origenDatosDto->data,$condata);      
                }       
            break;
            case TipoExtensionEnum::XLS:
                if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::ARCHIVO){
                    [$campos, $errorProceso] = $this->TrataFileExcel($origenDatosDto->data,TipoExtensionEnum::XLS,$condata);
                } else if ($origenDatosDto->tipoOrigen == TipoOrigenDatosEnum::URL){
                    [$campos, $errorProceso] = $this->TrataUrlExcel($origenDatosDto->data,TipoExtensionEnum::XLS, $condata);
                }         
            break;
        }
        // devolvemos la lista de campos , el error si lo ha habido
        return [$campos, $errorProceso];
    }

     /*
     * Descripción: Realiza el tratamiento de un XML desde un archivo cargado
     * 
     * Parametros: 
     *          data: nombre del archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataFileXML($data, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        // cargamos el contenido 
        $file = $this->projectDir . "/public/storage/default/{$data}";
        if (file_exists($file)) {
             // tratamos el contenido 
            [$campos, $data, $errorProceso] = $this->TrataCamposXML($file, $condata);
        }  else {
            $errorProceso ='El archivo no ha sido encontrado en el servidor';
        }  
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }

    /*
     * Descripción: Realiza el tratamiento de un XML desde una url
     * 
     * Parametros: 
     *          data: url
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataUrlXML($data, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        ini_set('auto_detect_line_endings', TRUE);
        $file = $data;
        // tratamos el contenido 
        [$campos, $data, $errorProceso] = $this->TrataCamposXML($file, $condata);
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }

     /*
     * Descripción: Realiza el tratamiento de un Json desde un archivo cargado
     * 
     * Parametros: 
     *          data: nombre del archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataFileJSON($data, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        $file = $this->projectDir . "/public/storage/default/{$data}";
        if (file_exists($file)) {
             // tratamos el contenido 
            [$campos, $data, $errorProceso] = $this->TrataCamposJSON($file, $condata);
        }  else {
            $errorProceso = 'El archivo no ha sido encontrado en el servidor';
        }  
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }

    /*
     * Descripción: Realiza el tratamiento de un json desde una url
     * 
     * Parametros: 
     *          data: url
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataUrlJSON($data, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        ini_set('auto_detect_line_endings', TRUE);
        $file = $data;
        // tratamos el contenido 
        [$campos, $data, $errorProceso] = $this->TrataCamposJSON($file, $condata);
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }

     /*
     * Descripción: Realiza el tratamiento de un xls y csv desde un archivo cargado
     * 
     * Parametros: 
     *          data: nombre del archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataFileExcel($data, $extension, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        $file = $this->projectDir . "/public/storage/default/{$data}";
        if (file_exists($file)) {
            // tratamos el contenido cambiando el reader del componete que lee excel 
            switch ($extension) {
                case TipoExtensionEnum::CSV:
                    $reader = new Csv();
                    [$campos, $data, $errorProceso] = $this->TrataCamposExcel($reader, $file, $condata);
                break;  
                case TipoExtensionEnum::XLS:
                    $reader = new Xls();
                    [$campos, $data, $errorProceso] = $this->TrataCamposExcel($reader, $file, $condata);
                break;
            }         
        }  else {
            $errorProceso = 'El archivo no ha sido encontrado en el servidor';
        }  
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }

    /*
     * Descripción: Realiza el tratamiento de un json desde una url
     * 
     * Parametros: 
     *          data: url
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataUrlExcel($data, $extension, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        ini_set('auto_detect_line_endings', TRUE);
        $file = $data;
        $ruta = pathinfo($file);
        $filename = "{$ruta['filename']}.{$ruta['extension']}";
        try{
            $data = file_get_contents($file);
        } catch(\Exception $ex){
            $errorProceso = $ex->getMessage();
        }
        if (empty($errorProceso)) {
            $this->defaultStorage->write($filename,  $data);
            $filename = $this->projectDir . "/public/storage/default/{$ruta['filename']}.{$ruta['extension']}";
            if (file_exists($filename)) {
                // tratamos el contenido cambiando el reader del componete que lee excel 
                switch ($extension) {
                    case TipoExtensionEnum::CSV:
                        $reader = new Csv();
                        [$campos, $data, $errorProceso] = $this->TrataCamposExcel($reader, $filename, $condata);
                    break;  
                    case TipoExtensionEnum::XLS:
                        $reader = new Xls();
                        [$campos, $data, $errorProceso] = $this->TrataCamposExcel($reader, $filename, $condata);
                    break;
                }         
            }  else {
                $errorProceso = "El archivo no ha sido encontrado en el servidor";
            }  
        }
        return ($condata) ? [$campos, $data, $errorProceso] : [$campos, $errorProceso];
    }


    /*
     * Descripción: Realiza el tratamiento de un XML
     * 
     * Parametros: 
     *          file: url o path donde está el archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataCamposXML($file, $condata) : array 
    {
        $campos = "";
        $errorProceso = "";
        $rows = null; 
        $data = null;
        try {
            //cargo el xml
            $rows = simplexml_load_file($file);
        }  catch(Exception $ex) {
             $errorProceso = "Error al validar formato XML" . $ex->getMessage();   
             return [$campos, $data, $errorProceso];
        }
        if (Count($rows)==0){
            //cuento las filas
            $errorProceso ="El XML no tiene filas";   
            return [$campos, $data, $errorProceso];
        } else {
            //extraigo los campos
            foreach($rows as $row){
                foreach($row as $colum){
                    $campos .= $colum->getName() .";";
                }
                break;
            }
             //extraigo las 100 primeras filas
            if ($condata) {
                $data = $this->xml2array($rows);
                $limite = (count($data)>100) ? 100 : count($data);
                $data = array_slice($data, 1,  $limite);
            }
        }
        if (empty($campos)) {
            $errorProceso ="El XML no tiene campos";   
            return [$campos, $data, $errorProceso]; 
        }  else {
            if (substr($campos, -1) == ";"){
                $campos = substr($campos , 0, -1);
            }
        }
        //devuelvo los resultados
        return [$campos,$data,$errorProceso];
    }

    /*
     * Descripción: Realiza el tratamiento de un XML
     * 
     * Parametros: 
     *          file: url o path donde está el archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataCamposJSON($file, $condata) : array
    {
        $campos = "";
        $data = null;
        $errorProceso = "";
        $rows= null; 
        $jsonFile = null;
        $string = "";
        try{
            //cargo el json
            $string = file_get_contents($file);
            $jsonFile = json_decode($string, true);
        }  catch(Exception $ex) {
            $errorProceso = "Error al validar formato JSON: " . $ex->getMessage();   
             return [$campos, $data, $errorProceso];
        }
        if ($jsonFile==null){
            $errorProceso = "Error al validar formato JSON";   
            return [$campos, $data, $errorProceso];
        }
        if (Count($jsonFile)==0){
            //cuento las filas
             $errorProceso = "El JSON no tiene filas";  
            return [$campos, $data, $errorProceso];  
        } else {
           //extraigo las campos
            foreach($jsonFile as $jsonRows){
                foreach($jsonRows as $jsonColum){
                    foreach(array_keys($jsonColum) as $key){
                        $campos .= $key . ";";
                    }
                    break;
                }
                break;
            }
            if ($condata) {
                //extraigo las 100 primeras filas
                $limite = (count(array_values($jsonFile)[0])>100) ? 100 : count($jsonFile);
                $data = array_slice(array_values($jsonFile)[0], 1,  $limite);  
            }
        } 
        if (empty($campos)) {
             $errorProceso = "El XML no tiene campos";  
            return [$campos, $data, $errorProceso];  
        } else {
            if (substr($campos, -1) == ";"){
                $campos = substr($campos , 0, -1);
            }
        }
        //devuelvo los resultados
        return [$campos, $data, $errorProceso];
    }

     /*
     * Descripción: Realiza el tratamiento de un csv y xls
     * 
     * Parametros: 
     *          reader: adaptador para la lectura del archivo por extension
     *          file: url o path donde está el archivo
     *          condata: indica si cargar muestra para ficha o no
     */
    private function TrataCamposExcel($reader, $file, $condata) : array 
    {
        $campos = "";
        $data = array();
        $errorProceso = "";   
        $worksheet = null;
        try{
            //cargo el contenido
            $spreadsheet = $reader->load($file);
            //pongo la solapa primera por defecto
            $worksheet = $spreadsheet->getActiveSheet();
        }  catch(Exception $ex) {
              $errorProceso = "Error al validar formato Excel:" . $ex->getMessage();  
             return [$campos, $data, $errorProceso];
        }
        $tieneFilas = false;
        //extraigo los campos
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); 
            $tieneFilas = true;
            foreach ($cellIterator as $cell) {
                $campos  .=  $cell->getValue() . ";";
            }
            break;
        }
        //extraigo las 100 primeras filas
        if ($condata) {
            $lastColumn = $worksheet->getHighestColumn();
            $lasfile = $worksheet->getHighestRow();
            $limite = ($lasfile>100) ? 100 :  $lasfile;
            $data = $worksheet->rangeToArray('A2:'.$lastColumn. $limite);
        }
        $campos = str_replace(";;","", $campos);
        $campos = str_replace(",",";", $campos);
        if (!$tieneFilas) {
             $errorProceso = "El excel no tiene filas";  
            return [$campos, $data, $errorProceso];  
        }
        if (empty($campos)) {
             $errorProceso = "El excel no tiene campos";  
            return [$campos, $data, $errorProceso];  
        } else {
            if (substr($campos, -1) == ";"){
                $campos = substr($campos , 0, -1);
            }
        }
        //devuelvo los datos
        return [$campos, $data, $errorProceso];
    }

     /*
     * Descripción: utilidad para poner un XML en un array
     * 
     * Parametros: 
     *          xmlObject: el contenido el xml
     *          out: el array e vuelta
     */
    function xml2array($xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = (is_object($node) ) ? $this->xml2array($node) : $node;
        return $out;
    }
}