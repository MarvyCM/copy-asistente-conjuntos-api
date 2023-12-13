<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class DescripcionDatosControllerTest extends WebTestCase
{
    private $accessToken="";
    private $client;
    private $id;

    public function setUp(): void
    {
        $this->Register();
        $this->GetLoginCheckAction();
        parent::setUp();
    }

    public function Register()
    {    
        self::ensureKernelShutdown();
        $this->client = static::createClient(); 
        self::ensureKernelShutdown();
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
             '{
                "username": "usuario@dominio.com",
                "password": "123456",
                "roles": "ROLE_USER"
              }'
        );
        $response = $this->client->getResponse()->getContent();
        $jsonContent = json_decode($response, 'json');
        $this->accessToken = "Bearer " . $jsonContent['token']; 
        $this->assertNotEmpty($jsonContent['token']);
    }

    public function GetLoginCheckAction()
    {    
        self::ensureKernelShutdown();
        $this->client = static::createClient(); 
        self::ensureKernelShutdown();
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
             '{
                "_username": "usuario@dominio.com",
                "_password": "123456"
              }'
        );
        $response = $this->client->getResponse()->getContent();
        $jsonContent = json_decode($response, 'json');
        $this->accessToken = "Bearer " . $jsonContent['token']; 
        $this->assertNotEmpty($jsonContent['token']);
    }

    public function testAltaDescripcionDatos()
    {
        $this->client->request(
            'POST',
            '/api/v1/descripciondatos',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
             'ACCEPT' => 'application/json',
             'HTTP_AUTHORIZATION' =>  $this->accessToken],
            '{
                "identificacion": "1dentificacion",
                "denominacion": "denominacion",
                "descripcion": "descripcion",
                "frecuenciaActulizacion": "Anual",
                "fechaInicio": "2021-01-19 00:00",
                "fechaFin": "2021-01-31 00:00",
                "territorio": "Aragon",
                "instancias": "instancias",
                "usuario": "sitomarmo@aragopedi.com",
                "sesion": "121342456",
                "estado": "BORRADOR",
                "estadoAlta": "1.1 descripcion"
              }'
        );
        
        $response=$this->client->getResponse()->getContent();
        $jsonContent = json_decode($response, 'json');
        $this->id = $jsonContent['id'];
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
 
        $url = "/api/v1/descripciondatos/". $this->id;
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
             'ACCEPT' => 'application/json',
             'HTTP_AUTHORIZATION' =>  $this->accessToken],
            '{
                "identificacion": "1dentificación",
                "denominacion": "denominacion",
                "descripcion": "descripcion",
                "frecuenciaActulizacion": "Anual",
                "fechaInicio": "2021-01-19 00:00:00",
                "fechaFin": "2021-01-31 00:00:00",
                "territorio": "Aragon",
                "instancias": "instancias",
                "usuario": "sitomarmo@aragopedi.com",
                "organoResponsable": "Ayuntamiento",
                "finalidad": "La finalidad es",
                "condiciones": "Real decreto de proteccion de datos",
                "vocabularios": "rojo;azul;verde",
                "servicios": "servicio1;servicio2",
                "vocabularios": "rojo;azul;verde",
                "etiquetas": "etiqueta1,etiqueta2",
                "estructura": "estructura",
                "estructuraDenominacion": "estructura Denominacion",
                "formatos": "xml;json",
                "estado": "EN_ESPERA",
                "estadoAlta": "1.1 descripcion",
                "sesion": "121342456"
              }'
        );  
        $this->assertEquals(202, $this->client->getResponse()->getStatusCode());

        $url = "/api/v1/descripciondatos/workflow/". $this->id;
        $this->client->request(
            'POST',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
             'ACCEPT' => 'application/json',
             'HTTP_AUTHORIZATION' =>  $this->accessToken],
            '{
                "usuario": "sitomarmo@aragopedi.com",
                "estado": "BORRADOR",
                "descripcion": "Descripcion del administrador ",
                "sesion": "121342456"
            }'
        );  
        $this->assertEquals(202, $this->client->getResponse()->getStatusCode());

        $url = "/api/v1/descripciondatos/" . $this->id;
        $this->client->request(
            'DELETE',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
             'ACCEPT' => 'application/json',
             'HTTP_AUTHORIZATION' =>  $this->accessToken],
            '{}'
        );  
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }
}