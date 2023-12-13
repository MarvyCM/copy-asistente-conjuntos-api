<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\DataBaseTool;
use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoBaseDatosEnum;

class DataBaseToolUnitTest extends TestCase
{
    public function testSuccessSqlSrvConection()
    {
        $cadena =  "";
        $errorProceso = "";
        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->host = "localhost";
        $origenDatosDto->servicio = "_";
        $origenDatosDto->esquema = "dummydb";
        $origenDatosDto->puerto = "1433";
        $origenDatosDto->tabla = "inventory";
        $origenDatosDto->usuarioDB = "sa";
        $origenDatosDto->contrasenaDB = "123456Sa@";
        $origenDatosDto->tipoBaseDatos = TipoBaseDatosEnum::SQLSERVER;

        $dataBaseTool = new DataBaseTool();
        [$cadena,$data, $errorProceso] = $dataBaseTool->PruebaConexion($origenDatosDto,1);

       $this->assertIsArray(explode(";",$cadena));

    }

    public function testSuccessMysqlConection()
    {
        $cadena =  "";
        $errorProceso = "";

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->host = "localhost";
        $origenDatosDto->servicio = "_";
        $origenDatosDto->esquema = "asistente_conjuntos";
        $origenDatosDto->puerto = "3306";
        $origenDatosDto->tabla = "user";
        $origenDatosDto->usuarioDB = "root";
        $origenDatosDto->contrasenaDB = "adminDP25@";
        $origenDatosDto->tipoBaseDatos = TipoBaseDatosEnum::MYSQL;
        
        $dataBaseTool = new DataBaseTool();
        [$cadena,$data,$errorProceso] = $dataBaseTool->PruebaConexion($origenDatosDto,1);

        $this->assertIsArray(explode(";",$cadena));
    }
/*
    public function testSuccessOracleConection()
    {
        $cadena =  "";
        $errorProceso = "";

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->host = "https://livesql.oracle.com/";
        $origenDatosDto->servicio = "_";
        $origenDatosDto->esquema = "WORLD_POPULATION";
        $origenDatosDto->puerto = "3306";
        $origenDatosDto->tabla = "WORLD_POPULATION";
        $origenDatosDto->usuarioDB = "sitomarmo@hotmail.com";
        $origenDatosDto->contrasenaDB = "S1t0m@rm021";
        $origenDatosDto->tipoBaseDatos = TipoBaseDatosEnum::ORACLE;
        
        $dataBaseTool = new DataBaseTool();
        $cadena = $dataBaseTool->PruebaConexion($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }
*/
    public function testSuccessProgreConection()
    {
        $cadena =  "";
        $errorProceso = "";

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->host = "localhost";
        $origenDatosDto->servicio = "_";
        $origenDatosDto->esquema = "asistente_conjuntos";
        $origenDatosDto->puerto = "5432";
        $origenDatosDto->tabla = "asistente_conjuntos.user";
        $origenDatosDto->usuarioDB = "alfonso";
        $origenDatosDto->contrasenaDB = "123456@SA";
        $origenDatosDto->tipoBaseDatos = TipoBaseDatosEnum::POSTGRESQL;
        
        $dataBaseTool = new DataBaseTool();
        [$cadena,$data, $errorProceso] = $dataBaseTool->PruebaConexion($origenDatosDto,1);

        $this->assertIsArray(explode(";",$cadena));
    }

}