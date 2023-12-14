<?php

namespace App\Tests\Service;

use App\Service\FileUploader;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

class FileUploaderUnitTest extends TestCase
{
    public function testSuccessUploadBase64File()
    {
        $base64Image = "data:image/jpeg;base64,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";


        $data = explode(',', $base64Image);
        /**
         * @var FilesystemOperator $filesystem
         */
        $filesystem = $this->getMockBuilder(FilesystemOperator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fileUploader = new FileUploader($filesystem);
        $filename = $fileUploader->uploadBase64File($base64Image);
        $this->assertNotEmpty($filename);
        $this->assertStringContainsString('.jpeg', $filename);
    }
}