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

        $this->utility = new ResponsiveImagesUtility;
        $this->inject($this->utility, 'imageService', $this->mockImageService());
        $this->inject($this->utility, 'objectManager', $this->mockObjectManager());
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
                return $test->mockFileObject($instructions);
            }));

        $imageServiceMock
            ->method('getImageUri')
            ->will($this->returnCallback(function ($file, $absolute) {
                return (($absolute) ? 'http://domain.tld' : '') . '/image@' . $file->getProperty('width') . '.jpg';
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

    protected function mockObjectManager()
    {
        $managerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $managerMock
            ->method('get')
            ->will($this->returnCallback(function ($className) {
                $arguments = func_get_args();
                array_shift($arguments);
                return new $className(...$arguments);
            }));

        return $managerMock;
    }
}
