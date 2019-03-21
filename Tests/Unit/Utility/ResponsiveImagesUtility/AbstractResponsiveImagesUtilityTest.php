<?php

namespace SMS\SmsResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use SMS\SmsResponsiveImages\Utility\ResponsiveImagesUtility;

abstract class AbstractResponsiveImagesUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->utility = new ResponsiveImagesUtility(
            $this->mockImageService()
        );
    }

    protected function mockImageService()
    {
        $test = $this;

        $imageServiceMock = $this->getMockBuilder(ImageService::class)
            ->setMethods(['applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->will($this->returnCallback(function ($file, $instructions) use ($test) {
                // Simulate processor_allowUpscaling = false
                $instructions['width'] = min($instructions['width'], $file->getProperty('width'));

                // Use extension from original image
                $instructions['extension'] = $file->getProperty('extension');

                return $test->mockFileObject($instructions);
            }));

        $imageServiceMock
            ->method('getImageUri')
            ->will($this->returnCallback(function ($file, $absolute) {
                return (($absolute) ? 'http://domain.tld' : '') . '/image-' . $file->getProperty('width')
                    . '.' . $file->getProperty('extension');
            }));

        return $imageServiceMock;
    }

    protected function mockFileObject($properties)
    {
        $fileMock = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                return $properties[$property];
            }));

        return $fileMock;
    }
}
