<?php
declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\Tests\Functional;

use Sitegeist\ResponsiveImages\Tests\Functional\ViewHelperTestCase;
use Sitegeist\ResponsiveImages\ViewHelpers\ImageViewHelper;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariant;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\Exception;

class ImageViewHelperTest extends ViewHelperTestCase
{
    public static function invalidArgumentsDataProvider(): array
    {
        return [
            [['src' => '', 'image' => null], 1517766588],
            [['src' => null, 'image' => null], 1517766588],
            [['src' => '', 'image' => null], 1517766588],
            [['src' => 'something', 'image' => 'something'], 1517766588],
            [['src' => 'something', 'image' => null, 'fileExtension' => 'dummy'], 1631539412],
        ];
    }

    /**
     * @test
     * @dataProvider invalidArgumentsDataProvider
     */
    public function renderThrowsExceptionOnInvalidArguments(array $arguments, int $expectedExceptionCode): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode($expectedExceptionCode);

        $viewHelper = new ImageViewHelper();
        $viewHelper->setArguments($arguments);
        $viewHelper->render();
    }

    public static function basicScalingCroppingDataProvider(): \Generator
    {
        yield 'original size' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" />',
            '@^<img src="(typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png)" width="400" height="300" alt="" />$@',
            400,
            300
        ];
        yield 'half width' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="200" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="150" alt="" />$@',
            200,
            150
        ];
        yield 'stretched' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="200" height="200" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="200" alt="" />$@',
            200,
            200
        ];
        yield 'cropped' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="200c" height="200c" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="200" alt="" />$@',
            200,
            200
        ];
        yield 'masked width' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="300m" height="300m" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="300" height="225" alt="" />$@',
            300,
            225
        ];
        yield 'masked height' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400m" height="150m" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="150" alt="" />$@',
            200,
            150
        ];
        // would be 200x150, but image will be stretched (why!?) up to have a width of 250
        // @todo remove multiple possible heights when dropping support for versions before TYPO3 13
        yield 'min width' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" height="150" minWidth="250" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="250" height="(188|150)" alt="" />$@',
            250,
            [188, 150]
        ];
        // would be 200x150, but image will be scaled down to have a width of 100
        yield 'max width' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" height="150" maxWidth="100" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="100" height="75" alt="" />$@',
            100,
            75
        ];
        // would be 200x150, but image will be stretched (why!?) up to have a height of 200
        // @todo remove multiple possible widths when dropping support for versions before TYPO3 13
        yield 'min height' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="200" minHeight="200" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="(267|200)" height="200" alt="" />$@',
            [267, 200],
            200,
            200
        ];
        // would be 200x150, but image will be scaled down to have a height of 75
        yield 'max height' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="200" maxHeight="75" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="100" height="75" alt="" />$@',
            100,
            75
        ];
    }

    /**
     * @TODO convert parameters to `int` only when dropping support for versions before TYPO3 13
     * @param int|int[] $expectedWidth
     * @param int|int[] $expectedHeight
     *
     * @test
     * @dataProvider basicScalingCroppingDataProvider
     */
    public function basicScalingCropping(string $template, string $expected, $expectedWidth, $expectedHeight): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);

        $coreTemplate = str_replace('<sms:image', '<f:image', $template);
        $coreView = GeneralUtility::makeInstance(StandaloneView::class);
        $coreView->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $coreTemplate);
        self::assertMatchesRegularExpression($expected, $coreView->render(), 'result of sms:image viewhelper does not match result of core viewhelper');

        $matches = [];
        preg_match($expected, $result, $matches);
        list($width, $height) = getimagesize($this->instancePath . '/' . $matches[1]);
        self::assertContains($width, is_array($expectedWidth) ? $expectedWidth : [$expectedWidth], 'width of generated image does not match expected width');
        self::assertContains($height, is_array($expectedHeight) ? $expectedHeight : [$expectedHeight], 'height of generated image does not match expected height');
    }

    public static function cropVariantCollectionDataProvider(): \Generator
    {
        yield 'default crop' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" crop="{crop}" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="225" alt="" />$@',
            200,
            225
        ];
        yield 'square crop' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" crop="{crop}" cropVariant="square" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="300" height="300" alt="" />$@',
            300,
            300
        ];
        yield 'wide crop' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" crop="{crop}" cropVariant="wide" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="400" height="200" alt="" />$@',
            400,
            200
        ];
    }

    /**
     * @test
     * @dataProvider cropVariantCollectionDataProvider
     */
    public function cropVariantCollection(string $template, string $expected, int $expectedWidth, int $expectedHeight): void
    {
        // Based on 400x300 dimensions
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('default', 'Default', new Area(0.25, 0.25, 0.5, 0.75)),
            new CropVariant('square', 'Square', new Area(0.125, 0, 0.75, 1)),
            new CropVariant('wide', 'Wide', new Area(0, 1 / 6, 1, 2 / 3)),
        ]);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);
        $view->assign('crop', (string) $cropVariantCollection);
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);

        $matches = [];
        preg_match($expected, $result, $matches);
        list($width, $height) = getimagesize($this->instancePath . '/' . $matches[1]);
        self::assertEquals($expectedWidth, $width, 'width of generated image does not match expected width');
        self::assertEquals($expectedHeight, $height, 'height of generated image does not match expected height');
    }

    public static function tagAttributesDataProvider(): \Generator
    {
        yield 'css' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" class="myClass" style="border: none" />',
            '<img class="myClass" style="border: none" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'loading' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" loading="lazy" />',
            '<img loading="lazy" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'decoding' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" decoding="async" />',
            '<img decoding="async" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'alt' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" alt="alternative text" />',
            '<img alt="alternative text" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" />'
        ];
        yield 'longdesc' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" longdesc="description" />',
            '<img longdesc="description" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'usemap' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" usemap="#map" />',
            '<img usemap="#map" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'default sizes' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="400w" />',
            '<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png 400w" sizes="(min-width: 400px) 400px, 100vw" width="400" height="300" alt="" />',
        ];
        yield 'sizes' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="400w" sizes="50vw" />',
            '<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png 400w" sizes="50vw" width="400" height="300" alt="" />',
        ];
    }

    /**
     * @test
     * @dataProvider tagAttributesDataProvider
     */
    public function tagAttributes(string $template, string $expected): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);
        self::assertEquals($expected, $view->render());
    }

    public static function imageWithSrcsetDataProvider(): \Generator
    {
        yield 'hdpi variants' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="1x, 2x" width="100" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" srcset="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 1x, (typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 2x" width="100" height="75" alt="" />$@',
            [
                1 => [100, 75],
                2 => [100, 75],
                3 => [200, 150]
            ]
        ];
        yield 'width variants' => [
            '<sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="100, 200" sizes="100vw" />',
            '@^<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" srcset="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 100w, (typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 200w, typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png 400w" sizes="100vw" width="400" height="300" alt="" />$@',
            [
                1 => [100, 75],
                2 => [200, 150]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider imageWithSrcsetDataProvider
     */
    public function imageWithSrcset(string $template, string $expected, array $expectedDimensions): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);

        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);

        $matches = [];
        preg_match($expected, $result, $matches);

        foreach ($expectedDimensions as $match => $imageDimension) {
            list($width, $height) = getimagesize($this->instancePath . '/' . $matches[$match]);
            self::assertEquals($imageDimension[0], $width, sprintf('width of image %d does not match expected width', $match));
            self::assertEquals($imageDimension[1], $height, sprintf('height of image %d does not match expected height', $match));
        }
    }

    public static function pictureTagDataProvider(): \Generator
    {
        yield [
            [
                [
                    'srcset' => '400',
                    'cropVariant' => 'wide',
                    'media' => '(min-width: 1000px)',
                    'sizes' => '100vw'
                ],
                [
                    'srcset' => '300',
                    'cropVariant' => 'square',
                    'media' => '(min-width: 700px)',
                    'sizes' => '50vw'
                ],
            ],
            '@^<picture><source srcset="typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png 400w" media="\(min-width: 1000px\)" sizes="100vw" /><source srcset="typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png 300w" media="\(min-width: 700px\)" sizes="50vw" /><img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" width="400" alt="" /></picture>$@',
        ];
    }

    /**
     * @test
     * @dataProvider pictureTagDataProvider
     */
    public function pictureTag(array $breakpoints, string $expected): void
    {
        // Based on 400x300 dimensions
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('default', 'Default', Area::createEmpty()),
            new CropVariant('square', 'Square', new Area(0.125, 0, 0.75, 1)),
            new CropVariant('wide', 'Wide', new Area(0, 1 / 6, 1, 2 / 3)),
        ]);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            ><sms:image src="EXT:sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" crop="{crop}" breakpoints="{breakpoints}" />');
        $view->assignMultiple([
            'crop' => (string) $cropVariantCollection,
            'breakpoints' => $breakpoints
        ]);
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);
    }

    public static function fileObjectsDataProvider(): \Generator
    {
        yield 'file in image argument' => [
            '<sms:image image="{file}" />',
            '@^<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" width="400" height="300" alt="" />$@'
        ];
        yield 'file reference in image argument' => [
            '<sms:image image="{fileReference}" />',
            '@^<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" width="400" height="300" alt="" />$@'
        ];
        yield 'file id in src argument' => [
            '<sms:image src="{file.uid}" />',
            '@^<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" width="400" height="300" alt="" />$@'
        ];
        /*
        // Currently not testible with simple TYPO3 API calls
        yield 'file reference id in src argument' => [
            '<sms:image src="{fileReference.uid}" treatIdAsReference="1" />',
            '@^<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png" width="400" height="300" alt="" />$@'
        ];
        */
        yield 'crop from file reference' => [
            '<sms:image image="{fileReference}" cropVariant="square" />',
            '@^<img src="typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png" width="300" height="300" alt="" />$@'
        ];
    }

    /**
     * @test
     * @dataProvider fileObjectsDataProvider
     */
    public function fileObjects(string $template, string $expected)
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);
        $view->assignMultiple($this->createFileObjects());
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);
    }
}
