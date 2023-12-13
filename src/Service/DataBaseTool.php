<?php

namespace App\Service;

use App\Form\Model\OrigenDatosDto;
use App\Entity\OrigenDatos;
use App\Enum\TipoBaseDatosEnum;
use Exception;
use \mysqli;

/*
 * Descripción: Es la utilidad que recibe el origen de datos el formato configuración Base datos
 *              Para los 4 tipos de base de datos Mysql, PostGresSQL, Oracle y SQLSever
*/
class DataBaseTool 
{

    public $origenDatosDto;

    public function __construct()
    {
 
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
        $origendatosDto = OrigenDatosDto::createFromOrigenDatos($origenDatos);
        return $this->PruebaConexion($origendatosDto, 100);  
    }

    /*
     * Descripción: Monta la cadena de conexión según el tipo de base datos y los parametros informados
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
    */
    public function CadenaConexion(OrigenDatosDto $origenDatosDto): string
    {

        $cadenaConexion = "";
        if (!(empty($origenDatosDto->host )  ||
            empty($origenDatosDto->servicio)  ||
            empty($origenDatosDto->esquema)   ||
            empty($origenDatosDto->puerto)   ||
            empty($origenDatosDto->tabla)   ||
            empty($origenDatosDto->usuarioDB)   ||
            empty($origenDatosDto->contrasenaDB))){

            switch ($origenDatosDto->tipoBaseDatos) {
                case TipoBaseDatosEnum::SQLSERVER:  
                    $cadenaConexion .= "Data Source={$origenDatosDto->host} Initial Catalog={$origenDatosDto->esquema}";
                    $cadenaConexion .= " User={$origenDatosDto->usuarioDB} Password Source={$origenDatosDto->contrasenaDB}";
                break;
                case TipoBaseDatosEnum::MYSQL:
                    $cadenaConexion .= "Server={$origenDatosDto->host};Port={$origenDatosDto->puerto}";
                    $cadenaConexion .= "Database={$origenDatosDto->esquema}";
                    $cadenaConexion .= "Uid={$origenDatosDto->usuarioDB};Pwd={$origenDatosDto->contrasenaDB}";
                break;
                case TipoBaseDatosEnum::ORACLE: 
                    $cadenaConexion .= "SERVER=(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)";
                    $cadenaConexion .= "(HOST={$origenDatosDto->host})(PORT={$origenDatosDto->puerto}))";
                    $cadenaConexion .= "(CONNECT_DATA=(SERVICE_NAME={$origenDatosDto->servicio})));";
                    $cadenaConexion .= "uid={$origenDatosDto->usuarioDB};pwd={$origenDatosDto->contrasenaDB};";    
                break;
                case TipoBaseDatosEnum::POSTGRESQL: 
                    $cadenaConexion .= "host={$origenDatosDto->host} dbname={$origenDatosDto->esquema} ";
                    $cadenaConexion .= "user={$origenDatosDto->usuarioDB} password={$origenDatosDto->contrasenaDB} ";
                    $cadenaConexion .= "port={$origenDatosDto->puerto}";    
                break;
            } 
        }
        return $cadenaConexion;
    }

    /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     *          limite:      numero de filas maximo en la muestra de datos 
    */
    public function PruebaConexion(OrigenDatosDto $origenDatosDto, int $limite): array
    {
        $campos = "";
        $data = array();
        $errorProceso = "";
        //según el tipo sde base de datos de lLama a su corespondiente funcion
        switch ($origenDatosDto->tipoBaseDatos) {
            case TipoBaseDatosEnum::SQLSERVER: 
                [$campos, $data, $errorProceso] = $this->PruebaConexionSQLSever($origenDatosDto,$limite);
            break;
            case TipoBaseDatosEnum::MYSQL:
                [$campos, $data, $errorProceso] = $this->PruebaConexionMysql($origenDatosDto,$limite);
            break;
            case TipoBaseDatosEnum::ORACLE: 
                [$campos, $data, $errorProceso] = $this->PruebaConexionOracle($origenDatosDto,$limite);
            break;
            case TipoBaseDatosEnum::POSTGRESQL: 
                [$campos, $data, $errorProceso] = $this->PruebaConexionPostgres($origenDatosDto,$limite);
            break;
        } 
        //devuelvo los campos, la muestra datos y/o el error
        return [$campos, $data, $errorProceso];   
    }


    /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              para bases de datos SQLServer
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     *          limite:      numero de filas maximo en la muestra de datos 
     *
    */ 
    public function PruebaConexionSQLSever(OrigenDatosDto $origenDatosDto, int $limite): array
    {
        $campos = "";
        $data = array();
        $errorProceso = "";
        if (!(empty($origenDatosDto->host )  ||
        empty($origenDatosDto->puerto)   ||
        empty($origenDatosDto->esquema)   ||
        empty($origenDatosDto->tabla)   ||
        empty($origenDatosDto->usuarioDB)   ||
        empty($origenDatosDto->contrasenaDB))){
            $serverName = "{$origenDatosDto->host}, {$origenDatosDto->puerto}"; //serverName\instanceName, portNumber (por defecto es 1433)
            $connectionInfo = array("Database"=>$origenDatosDto->esquema, "UID"=>$origenDatosDto->usuarioDB, "PWD"=>$origenDatosDto->contrasenaDB);
            $conn = false;
            try{
                //creo la conexión
                $conn = sqlsrv_connect($serverName, $connectionInfo);
            } catch(Exception $ex) {
                $errorProceso ="Fallo en la conexión a SQLServer";
            }
            try{
                if($conn === false ) {
                    $errorProceso ="Fallo en la conexión a SQLServer";
                } else {   
                     //genero la consula SQL
                    $sql = "SELECT top({$limite}) * FROM {$origenDatosDto->tabla}";
                    //lanzo la consulta
                    $res = sqlsrv_query($conn, $sql, array(), array( "QueryTimeout" => 5,"Scrollable" => 'static' ));
                    if( $res === false )  {  
                        $errorProceso = "Error en la consulta sql, revise el nombre tabla o vista";  
                    } else {
                        $numeroFilas = sqlsrv_num_rows($res);
                        $rows = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
                        if( $numeroFilas == 0)  {  
                            $errorProceso ="Error al recorrer las filas";
                        } else {
                            //recojo los campos
                            foreach(array_keys($rows) as $key){
                                $campos .= $key . ";";
                            }
                            $campos = substr($campos, 0, -1);
                            //recojo las 100 primeras filas
                            $limite = $numeroFilas-1;
                            for($i = 0; $i <= $limite; $i++) {
                                $data[$i] = array_values($rows);
                                $rows = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
                            }
                            if (empty($campos)) {
                                $errorProceso ="No hay datos en la tabla o vista {$origenDatosDto->tabla}";   
                            }
                        } 
                    }
                } 
            } catch(Exception $ex) {
                $errorProceso ="Fallo en la consulta a tabla o vista";
            }
           //libero conexión
            if (!empty($res)){
                sqlsrv_free_stmt($res);
            }
            if($conn !== false ){
                sqlsrv_close($conn);
            }
        }
        return [$campos, $data, $errorProceso];
    }

    /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              para bases de datos Mysql
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     *          limite:      numero de filas maximo en la muestra de datos 
     *
    */ 
    public function PruebaConexionMysql(OrigenDatosDto $origenDatosDto, int $limite): array
    {
        $link = null;
        $campos = "";
        $data = array();
        $errorProceso = "";
        try{
            //creo la conexión
            $link = mysqli_connect($origenDatosDto->host, 
            $origenDatosDto->usuarioDB,
            $origenDatosDto->contrasenaDB,
            $origenDatosDto->esquema,
            $origenDatosDto->puerto) or die("Unable to Connect to '{$origenDatosDto->host}'");
            mysqli_select_db($link, $origenDatosDto->esquema) or die("Could not open the db '{$origenDatosDto->esquema}}'");
        } catch(Exception $ex) {
            $errorProceso ="Fallo en la conexión a mysql";
        }
        try{
            if ($link === false) {
                $errorProceso ="Fallo en la conexión a mysql";
            } else {
                //genero la consula SQL
                $test_query = "SELECT * FROM {$origenDatosDto->tabla} limit {$limite}";
                $res = mysqli_query($link, $test_query);
                if( $res === false )  {  
                     $errorProceso ="Error en la consulta sql, revise el nombre tabla o vista"; 
                } else {
                    //lanzo la consulta
                    $rows = mysqli_fetch_array($res,MYSQLI_ASSOC);
                    $numeroFilas = mysqli_num_rows($res);
                    if($numeroFilas == 0)  {  
                         $errorProceso ="No hay datos en la tabla o vista {$origenDatosDto->tabla}";       
                    } else {
                         //recojo los campos
                        foreach(array_keys($rows) as $key){
                            $campos .= $key . ";";
                        }
                        //recojo las 100 primeras filas
                        $campos = substr($campos, 0, -1);   
                        $limite = $numeroFilas-1;
                        for($i = 0; $i <= $limite; $i++) {
                            $data[$i] =array_values($rows);
                            $rows = mysqli_fetch_array($res,MYSQLI_ASSOC);
                        }
                        if (empty($campos)) {
                            $errorProceso ="La tabla o vista no tiene campos";   
                        }
                    }
                }  
            }        
        } catch(Exception $ex) {
            $errorProceso ="Fallo en la consulta a tabla o vista";  
        }
        //libero conexión
        if (!empty($res)){
            mysqli_free_result($res);
        }
        if (!empty($link)) {
            mysqli_close($link);
        }
        return [$campos, $data, $errorProceso];
    }

    /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              para bases de datos Oracle
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     *          limite:      numero de filas maximo en la muestra de datos 
     *
     */ 
    public function PruebaConexionOracle(OrigenDatosDto $origenDatosDto, int $limite): array
    {
        $campos = "";
        $data = array();
        $errorProceso = "";
        $conectionstring = "//{$origenDatosDto->host}:{$origenDatosDto->puerto}/{$origenDatosDto->servicio}";
        $conn = null;
        try{
             //creo la conexión
            $conn = oci_connect($origenDatosDto->usuarioDB, 
                                $origenDatosDto->contrasenaDB,
                                $conectionstring);

        } catch(Exception $ex) {
           $errorProceso ="Fallo en la conexión a Oracle";
        }
        try {
            if ($conn === false) {
                $errorProceso ="Fallo en la conexión a Oracle";
            } else {
                //genero la consula SQL
                $stid = oci_parse($conn, "SELECT * FROM {$origenDatosDto->tabla} WHERE rownum = {$limite}");
                if ($stid == false) { 
                    $errorProceso ="Fallo en la conexión a Oracle"; 
                } else {
                    //lanzo la consulta
                    oci_execute($stid, OCI_DESCRIBE_ONLY);
                    $numeroFilas = oci_num_fields($stid);
                    if($numeroFilas == 0)  {  
                        $errorProceso ="No hay datos en la tabla o vista {$origenDatosDto->tabla}"; 
                    } else {
                        $rows = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                        foreach(array_keys($rows[0]) as $key){
                            $campos .= $key . ";";
                        }
                        //recojo los campos
                        $campos = substr($campos, 0, -1); 
                        $limite = $numeroFilas-1;
                        //recojo las 100 primeras filas
                        for($i = 0; $i <= $limite; $i++) {
                            $data[$i] =array_values($rows);
                            $rows = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
                        }
                        if (empty($campos)) {
                            $errorProceso ="La tabla o vista no tiene campos";   
                        }
                    }  
                }          
            }
        }catch(Exception $ex) {
            $errorProceso ="Fallo en la consulta a tabla o vista";
        }
        //libero conexión
        if (!empty($stid)){
            oci_free_statement($stid);
        }
        if (!empty($conn)) {
            pg_close($conn);
        }            
        return [$campos, $data, $errorProceso];
    }

    /*
     * Descripción: Realiza la comprobación de la integridad de los datos, extrae los campos
     *              para bases de datos PostGress
     * 
     * Parametros: 
     *          origenDatos: objeto con el origen de los datos
     *          limite:      numero de filas maximo en la muestra de datos 
     *
    */ 
    public function PruebaConexionPostgres(OrigenDatosDto $origenDatosDto, int $limite): array
    {
        $campos = "";
        $data = array();
        $errorProceso = "";
        $connStr = "host={$origenDatosDto->host} port={$origenDatosDto->puerto} ";
        $connStr .= "dbname={$origenDatosDto->esquema} ";
        $connStr .= "user={$origenDatosDto->usuarioDB} password={$origenDatosDto->contrasenaDB}";
        //simple check
        $conn = null;
        try{
            //creo la conexión
             $conn = pg_connect($connStr);
        } catch(Exception $ex) {
           $errorProceso ="Fallo en la conexión a PostgesSQL";
        }
        try{
            if ($conn === false) {
                $errorProceso ="Fallo en la conexión a Postgress";
            } else {
                //genero la consula SQL y lanzo 
                $res = pg_query($conn, "SELECT * FROM {$origenDatosDto->tabla} LIMIT {$limite};");
                if( $res === false )  {  
                     $errorProceso ="Fallo en la conexión a PostgresSQL"; 
                } else {
                    $rows = pg_fetch_array($res,0,PGSQL_ASSOC);
                    $numeroFilas = pg_num_rows($res);
                    if( $numeroFilas == 0 )  {  
                         $errorProceso ="No hay datos en la tabla o vista {$origenDatosDto->tabla}"; 
                    } else {
                        foreach(array_keys($rows) as $key){
                            $campos .= $key . ";";
                        }
                        //recojo los campos
                        $campos = substr($campos, 0, -1); 
                        $limite = $numeroFilas-1;
                        //recojo las 100 primeras filas
                        for($i = 0; $i <= $limite; $i++) {
                            $data[$i] =array_values($rows);
                            $rows = pg_fetch_array($res,null,PGSQL_ASSOC);
                        }
                        if (empty($campos)) {
                            $errorProceso ="La tabla o vista no tiene campos";   
                        }
                    }
                }   
            }       
        } catch(Exception $ex) {
            $errorProceso ="Fallo en la consulta a tabla o vista";
        }
        //libero conexión
        if (!empty($res)){
            pg_free_result($res);
        }
        if (!empty($conn)) {
            pg_close($conn);
        }
        return [$campos, $data, $errorProceso];
    }
}