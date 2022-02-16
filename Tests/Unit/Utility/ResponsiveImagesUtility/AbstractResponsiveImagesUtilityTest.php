<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use Sitegeist\ResponsiveImages\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;

abstract class AbstractResponsiveImagesUtilityTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected $resetSingletonInstances = true;

    protected function setUp(): void
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
            ->disableOriginalConstructor()
            ->setMethods(['applyProcessingInstructions', 'getImageUri'])
            ->getMock();

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->will($this->returnCallback(function ($file, $instructions) use ($test) {
                // Simulate processor_allowUpscaling = false
                $instructions['width'] = isset($instructions['width'])
                    ? min($instructions['width'], $file->getProperty('width'))
                    : $file->getProperty('width');

                // Use file name and extension from original image
                $instructions['name'] = $file->getProperty('name');

                if (isset($instructions['fileExtension'])) {
                    $instructions['extension'] = $instructions['fileExtension'];
                    $instructions['mimeType'] = 'image/' . $instructions['fileExtension'];
                    unset($instructions['fileExtension']);
                } else {
                    $instructions['extension'] = $file->getProperty('extension');
                    $instructions['mimeType'] = $file->getProperty('mimeType');
                }

                return $test->mockFileObject($instructions, true);
            }));

        $imageServiceMock
            ->method('getImageUri')
            ->will($this->returnCallback(function ($file, $absolute) {
                return (($absolute) ? 'http://domain.tld' : '') . '/' . $file->getProperty('name') . '-' . $file->getProperty('width')
                    . '.' . $file->getProperty('extension');
            }));

        return $imageServiceMock;
    }

    protected function mockFileObject($properties, $processed = false)
    {
        $defaultProperties = [
            'name' => 'image'
        ];
        $properties = array_replace($defaultProperties, $properties);

        $fileMock = $this->getMockBuilder($processed ? ProcessedFile::class : FileReference::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProperty', 'getMimeType', 'getContents', 'getSha1'])
            ->getMock();

        $fileMock
            ->method('getProperty')
            ->will($this->returnCallback(function ($property) use ($properties) {
                return $properties[$property] ?? null;
            }));
        $fileMock
            ->method('getMimeType')
            ->will($this->returnCallback(function () use ($properties) {
                return $properties['mimeType'];
            }));
        $fileMock
            ->method('getContents')
            ->will($this->returnCallback(function () use ($properties) {
                return 'das-ist-der-dateiinhalt';
            }));
        $fileMock
            ->method('getSha1')
            ->will($this->returnCallback(function () use ($properties) {
                return 'das-ist-der-sha1-hash';
            }));

        return $fileMock;
    }
}
