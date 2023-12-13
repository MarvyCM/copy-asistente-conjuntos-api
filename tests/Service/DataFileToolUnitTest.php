<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

use App\Service\DataFileTool;
use App\Form\Model\OrigenDatosDto;
use App\Enum\TipoBaseDatosEnum;
use League\Flysystem\FilesystemOperator;

use App\Enum\TipoExtensionEnum;
use App\Enum\TipoOrigenDatosEnum;
class DataFileToolUnitTest extends WebTestCase
{

     public function testSuccessXMLFile()
    {

        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");
        $origenDatosDto = new OrigenDatosDto();

        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::ARCHIVO;
        $origenDatosDto->data = "Libro1.xml";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }

    public function testSuccessXMLUrl()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectDir = "";

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $origenDatosDto->data = "http://localhost:8080/storage/default/Libro1.xml";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));

    }
    public function testSuccessCSVFile()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");   

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::ARCHIVO;
        $origenDatosDto->data = "Libro1.csv";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }

    public function testSuccessCSVUrl()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");   

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $origenDatosDto->data = "http://localhost:8080/storage/default/Libro1.csv";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }

    public function testSuccessJsonFile()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();
 
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::ARCHIVO;
        $origenDatosDto->data = "Libro1.json";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }

    public function testSuccessJsonUrl()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectDir = "";
        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $origenDatosDto->data = "http://localhost:8080/storage/default/Libro1.json";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }


    public function testSuccessXlsFile()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::ARCHIVO;
        $origenDatosDto->data = "Libro1.xls";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }

    public function testSuccessXlsUrl()
    {
        $cadena =  "";
        $errorProceso = "";
         /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $projectDir =  $container->getParameter("projectDir");

        $origenDatosDto = new OrigenDatosDto();
        $origenDatosDto->tipoOrigen = TipoOrigenDatosEnum::URL;
        $origenDatosDto->data = "http://localhost:8080/storage/default/Libro1.xls";

        $dataFileTool = new DataFileTool($projectDir, $filesystem);
        [$cadena,$errorProceso] = $dataFileTool->PruebaIntegridadDatos($origenDatosDto);

        $this->assertIsArray(explode(";",$cadena));
    }
}
