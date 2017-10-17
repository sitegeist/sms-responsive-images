<?php

namespace SMS\SmsResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

class ImageTagTest extends AbstractResponsiveImagesUtilityTest
{
    public function createImageTagWithSrcsetUsingEmptySrcsetProvider()
    {
        return [
            // Test plain tag
            'usingEmptySrcset' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [],
                'img',
                1000,
                1000,
                null,
                '/image@1000.jpg 1000w'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetUsingEmptySrcsetProvider
     */
    public function createImageTagWithSrcsetUsingEmptySrcset(
        $originalImage,
        $fallbackImage,
        $srcsetConfig,
        $tagName,
        $widthAttribute,
        $heightAttribute,
        $srcAttribute,
        $srcsetAttribute
    ) {
        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, $srcsetConfig);
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($widthAttribute, $tag->getAttribute('width'));
        $this->assertEquals($heightAttribute, $tag->getAttribute('height'));
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
    }

    public function createImageTagWithSrcsetProvider()
    {
        return [
            // Test standard output (instead of picturefill output)
            'usingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400],
                '/image@1000.jpg',
                '/image@400.jpg 400w, /image@1000.jpg 1000w',
                false
            ],
            // Test srcset with 3 widths, one having same width as fallback
            'usingThreeWidthsWithFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400, 800, 1000],
                null,
                '/image@400.jpg 400w, /image@800.jpg 800w, /image@1000.jpg 1000w',
                true
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400, 800],
                null,
                '/image@400.jpg 400w, /image@800.jpg 800w, /image@1000.jpg 1000w',
                true
            ],
            // Test high dpi
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                ['1x', '2x'],
                null,
                '/image@1000.jpg 1x, /image@2000.jpg 2x',
                true
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetProvider
     */
    public function createImageTagWithSrcset(
        $originalImage,
        $fallbackImage,
        $srcsetConfig,
        $srcAttribute,
        $srcsetAttribute,
        $picturefillMarkup
    ) {
        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig,
            null,
            null,
            null,
            null,
            $picturefillMarkup
        );
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
    }

    public function createImageTagWithSrcsetAndFocusAreaProvider()
    {
        return [
            'usingFocusArea' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                new Area(0.4, 0.4, 0.6, 0.6),
                htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600]))
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetAndFocusAreaProvider
     */
    public function createImageTagWithSrcsetAndFocusArea(
        $originalImage,
        $fallbackImage,
        $focusArea,
        $focusAreaAttribute
    ) {
        // Test focus area attribute
        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, [], null, $focusArea);
        $this->assertEquals($focusAreaAttribute, $tag->getAttribute('data-focus-area'));
    }

    public function createImageTagWithSrcsetAndSizesProvider()
    {
        return [
            // Test sizes attribute
            'usingStaticQuery' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400],
                'sizes query',
                'sizes query'
            ],
            // Test sizes attribute with dynamic width
            'usingDynamicQuery' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400],
                '%1$d',
                1000
            ],
            // Test sizes attribute for high dpi setup
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                ['1x', '2x'],
                '%1$d',
                null
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetAndSizesProvider
     */
    public function createImageTagWithSrcsetAndSizes(
        $originalImage,
        $fallbackImage,
        $srcsetConfig,
        $sizesConfig,
        $sizesAttribute
    ) {
        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig,
            null,
            null,
            $sizesConfig
        );
        $this->assertEquals($sizesAttribute, $tag->getAttribute('sizes'));
    }

    public function createImageTagWithSrcsetAndMetadataProvider()
    {
        return [
            // Test image metadata attributes
            'usingMetadata' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                'image alt',
                'image title'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetAndMetadataProvider
     */
    public function createImageTagWithSrcsetAndMetadata($originalImage, $fallbackImage, $altAttribute, $titleAttribute)
    {
        // Test image metadata attributes
        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, []);
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($titleAttribute, $tag->getAttribute('title'));
    }

    public function createImageTagWithSrcsetBasedOnCustomTagProvider()
    {
        // Test if original tag attributes persist
        $customTag = new TagBuilder('img');
        $customTag->addAttribute('alt', 'fixed alt');
        $customTag->addAttribute('longdesc', 'long description');

        return [
            'usingCustomTag' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alt' => 'image alt', 'title' => 'image title']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                $customTag,
                'fixed alt',
                'long description'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetBasedOnCustomTagProvider
     */
    public function createImageTagWithSrcsetBasedOnCustomTag(
        $originalImage,
        $fallbackImage,
        $customTag,
        $altAttribute,
        $longdescAttribute
    ) {
        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            [],
            null,
            null,
            null,
            $customTag
        );
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($longdescAttribute, $tag->getAttribute('longdesc'));
    }

    public function createImageTagWithSrcsetAndLazyloadProvider()
    {
        return [
            // Test standard output (instead of picturefill output)
            'usingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400],
                null,
                null,
                '/image@1000.jpg',
                '/image@400.jpg 400w, /image@1000.jpg 1000w',
                false
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [400, 800],
                null,
                null,
                null,
                '/image@400.jpg 400w, /image@800.jpg 800w, /image@1000.jpg 1000w',
                true
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetAndLazyloadProvider
     */
    public function createImageTagWithSrcsetAndLazyload(
        $originalImage,
        $fallbackImage,
        $srcsetConfig,
        $srcAttribute,
        $srcsetAttribute,
        $dataSrcAttribute,
        $dataSrcsetAttribute,
        $picturefillMarkup
    ) {
        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig,
            null,
            null,
            null,
            null,
            $picturefillMarkup,
            false,
            true
        );
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($dataSrcAttribute, $tag->getAttribute('data-src'));
        $this->assertEquals($dataSrcsetAttribute, $tag->getAttribute('data-srcset'));
    }
}
