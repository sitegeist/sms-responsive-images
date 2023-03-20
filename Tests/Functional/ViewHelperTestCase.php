<?php
declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\Tests\Functional;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariant;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class ViewHelperTestCase extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sms_responsive_images'
    ];

    protected function createFileObjects(): array
    {
        $variables = [];
        $resourceFactory = $this->get(ResourceFactory::class);

        // Create file record from existing test file
        $variables['file'] = $resourceFactory->retrieveFileOrFolderObject(
            'EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png'
        );

        // Create file reference with cropping information
        // Based on 400x300 dimensions
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('default', 'Default', Area::createEmpty()),
            new CropVariant('square', 'Square', new Area(0.125, 0, 0.75, 1)),
            new CropVariant('wide', 'Wide', new Area(0, 1 / 6, 1, 2 / 3)),
        ]);
        $variables['fileReference'] = $resourceFactory->createFileReferenceObject([
            'crop' => (string) $cropVariantCollection,
            'uid_local' => $variables['file']->getUid(),
            'alternative' => '',
            'title' => ''
        ]);

        return $variables;
    }
}
