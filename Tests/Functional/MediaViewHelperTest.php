<?php
declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\Tests\Functional;

use Sitegeist\ResponsiveImages\Tests\Functional\ViewHelperTestCase;
use Sitegeist\ResponsiveImages\ViewHelpers\MediaViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\Exception;

class MediaViewHelperTest extends ViewHelperTestCase
{
    /**
     * @test
     */
    public function renderThrowsExceptionOnInvalidObject(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1678270961);

        $viewHelper = new MediaViewHelper();
        $viewHelper->setArguments(['file' => new \stdClass]);
        $viewHelper->render();
    }

    /**
     * @test
     */
    public function renderThrowsExceptionOnInvalidFileExtension(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1631539412);

        $fileObjects = $this->createFileObjects();

        $viewHelper = new MediaViewHelper();
        $viewHelper->setArguments(['file' => $fileObjects['file'], 'image' => null, 'fileExtension' => 'dummy']);
        $viewHelper->render();
    }

    public static function basicScalingCroppingDataProvider(): \Generator
    {
        yield 'original size' => [
            '<sms:media file="{file}" />',
            '@^<img src="(typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest\.png)" width="400" height="300" alt="" />$@',
            400,
            300
        ];
        yield 'half width' => [
            '<sms:media file="{file}" width="200" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="150" alt="" />$@',
            200,
            150
        ];
        yield 'stretched' => [
            '<sms:media file="{file}" width="200" height="200" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="200" alt="" />$@',
            200,
            200
        ];
        yield 'cropped' => [
            '<sms:media file="{file}" width="200c" height="200c" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="200" alt="" />$@',
            200,
            200
        ];
        yield 'masked width' => [
            '<sms:media file="{file}" width="300m" height="300m" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="300" height="225" alt="" />$@',
            300,
            225
        ];
        yield 'masked height' => [
            '<sms:media file="{file}" width="400m" height="150m" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" width="200" height="150" alt="" />$@',
            200,
            150
        ];
    }

    /**
     * @test
     * @dataProvider basicScalingCroppingDataProvider
     */
    public function basicScalingCropping(string $template, string $expected, int $expectedWidth, int $expectedHeight): void
    {
        $fileObjects = $this->createFileObjects();

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $template);
        $view->assignMultiple($fileObjects);
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);

        $coreTemplate = str_replace('<sms:image', '<f:image', $template);
        $coreView = GeneralUtility::makeInstance(StandaloneView::class);
        $coreView->assignMultiple($fileObjects);
        $coreView->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            data-namespace-typo3-fluid="true"
            >' . $coreTemplate);
        self::assertMatchesRegularExpression($expected, $coreView->render(), 'result of sms:image viewhelper does not match result of core viewhelper');

        $matches = [];
        preg_match($expected, $result, $matches);
        list($width, $height) = getimagesize($this->instancePath . '/' . $matches[1]);
        self::assertEquals($expectedWidth, $width, 'width of generated image does not match expected width');
        self::assertEquals($expectedHeight, $height, 'height of generated image does not match expected height');
    }

    public static function tagAttributesDataProvider(): \Generator
    {
        yield 'css' => [
            '<sms:media file="{file}" class="myClass" style="border: none" />',
            '<img class="myClass" style="border: none" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" alt="" />'
        ];
        yield 'loading' => [
            '<sms:media file="{file}" loading="lazy" />',
            '<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" loading="lazy" alt="" />'
        ];
        yield 'decoding' => [
            '<sms:media file="{file}" decoding="async" />',
            '<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" decoding="async" alt="" />'
        ];
        yield 'alt' => [
            '<sms:media file="{file}" alt="alternative text" />',
            '<img alt="alternative text" src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" width="400" height="300" />'
        ];
        yield 'default sizes' => [
            '<sms:media file="{file}" srcset="400w" />',
            '<img src="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png" srcset="typo3conf/ext/sms_responsive_images/Tests/Functional/Fixtures/ImageViewHelperTest.png 400w" sizes="(min-width: 400px) 400px, 100vw" width="400" height="300" alt="" />',
        ];
        yield 'sizes' => [
            '<sms:media file="{file}" srcset="400w" sizes="50vw" />',
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
        $view->assignMultiple($this->createFileObjects());
        self::assertEquals($expected, $view->render());
    }

    public static function imageWithSrcsetDataProvider(): \Generator
    {
        yield 'hdpi variants' => [
            '<sms:media file="{file}" srcset="1x, 2x" width="100" />',
            '@^<img src="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png)" srcset="(typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 1x, (typo3temp/assets/_processed_/4/5/csm_ImageViewHelperTest_.*\.png) 2x" width="100" height="75" alt="" />$@',
            [
                1 => [100, 75],
                2 => [100, 75],
                3 => [200, 150]
            ]
        ];
        yield 'width variants' => [
            '<sms:media file="{file}" srcset="100, 200" sizes="100vw" />',
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

        $view->assignMultiple($this->createFileObjects());
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
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<html
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:sms="http://typo3.org/ns/Sitegeist/ResponsiveImages/ViewHelpers"
            data-namespace-typo3-fluid="true"
            ><sms:media file="{fileReference}" breakpoints="{breakpoints}" />');
        $view->assignMultiple([
            ...$this->createFileObjects(),
            'breakpoints' => $breakpoints
        ]);
        $result = $view->render();
        self::assertMatchesRegularExpression($expected, $result);
    }
}
