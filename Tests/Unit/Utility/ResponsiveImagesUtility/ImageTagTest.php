<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

class ImageTagTest extends AbstractResponsiveImagesUtilityTest
{
    public function createSimpleImageTagProvider()
    {
        return [
            'simple' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                false,
                false,
                0,
                false,
                'img',
                '/image-2000.jpg',
                null,
                null,
                null,
                null
            ],
            'usingMetadata' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                false,
                false,
                0,
                false,
                'img',
                '/image-2000.jpg',
                null,
                null,
                'image alt',
                'image title'
            ],
            'usingPredefinedTag' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new TagBuilder('img-test'),
                null,
                false,
                false,
                0,
                false,
                'img-test',
                '/image-2000.jpg',
                null,
                null,
                null,
                null
            ],
            'usingFocusArea' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                new Area(0.4, 0.4, 0.6, 0.6),
                false,
                false,
                0,
                false,
                'img',
                '/image-2000.jpg',
                null,
                htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600])),
                null,
                null
            ],
            'usingAbsoluteUri' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                true,
                false,
                0,
                false,
                'img',
                'http://domain.tld/image-2000.jpg',
                null,
                null,
                null,
                null
            ],
            'usingLazyload' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                false,
                true,
                0,
                false,
                'img',
                null,
                '/image-2000.jpg',
                null,
                null,
                null
            ],
            'usingLazyloadWithPlaceholder' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                false,
                true,
                20,
                false,
                'img',
                '/image-20.jpg',
                '/image-2000.jpg',
                null,
                null,
                null
            ],
            'usingLazyloadWithPlaceholderInline' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null,
                false,
                true,
                20,
                true,
                'img',
                'data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                '/image-2000.jpg',
                null,
                null,
                null
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createSimpleImageTagProvider
     */
    public function createSimpleImageTag(
        $originalImage,
        $fallbackImage,
        $tag,
        $focusArea,
        $absoluteUri,
        $lazyload,
        $placeholderSize,
        $placeholderInline,
        $tagName,
        $srcAttribute,
        $dataSrcAttribute,
        $dataFocusAreaAttribute,
        $altAttribute,
        $titleAttribute
    ) {
        $tag = $this->utility->createSimpleImageTag(
            $originalImage,
            $fallbackImage,
            $tag,
            $focusArea,
            $absoluteUri,
            $lazyload,
            $placeholderSize,
            $placeholderInline
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($dataSrcAttribute, $tag->getAttribute('data-src'));
        $this->assertEquals($dataFocusAreaAttribute, $tag->getAttribute('data-focus-area'));
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($titleAttribute, $tag->getAttribute('title'));
    }

    public function createImageTagWithSrcsetUsingEmptySrcsetProvider()
    {
        return [
            // Test plain tag
            'usingEmptySrcset' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [],
                'img',
                1000,
                1000,
                null,
                '/image-1000.jpg 1000w'
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
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400],
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                false
            ],
            // Test srcset with 3 widths, one having same width as fallback
            'usingThreeWidthsWithFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400, 800, 1000],
                null,
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w',
                true
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400, 800],
                null,
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w',
                true
            ],
            // Test high dpi
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                ['1x', '2x'],
                null,
                '/image-1000.jpg 1x, /image-2000.jpg 2x',
                true
            ],
            // Test svg image
            'usingSvg' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'svg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'svg']),
                [100, 200, 300],
                '/image-2000.svg',
                null,
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
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
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
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400],
                'sizes query',
                'sizes query'
            ],
            // Test sizes attribute with dynamic width
            'usingDynamicQuery' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400],
                '%1$d',
                1000
            ],
            // Test sizes attribute for high dpi setup
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
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
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                'image alt',
                'image title'
            ],
            // Test svg image metadata attributes
            'usingMetadataWithSvg' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'svg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'svg']),
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
                    ['width' => 2000, 'height' => 2000, 'alt' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                $customTag,
                'fixed alt',
                'long description'
            ],
            'usingCustomTagWithSvg' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alt' => 'image alt', 'title' => 'image title', 'extension' => 'svg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'svg']),
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
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400],
                null,
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                false,
                0,
                false
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400, 800],
                null,
                null,
                null,
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w',
                true,
                0,
                false
            ],
            // Test svg image
            'withSvgImage' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'svg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'svg']),
                [400, 800],
                null,
                null,
                '/image-2000.svg',
                null,
                true,
                0,
                false
            ],
            // Test placeholder image
            'usingPlaceholderImage' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [400],
                '/image-20.jpg',
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                false,
                20,
                false
            ],
            // Test placeholder image inline
            'usingPlaceholderImageInline' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg']),
                [400],
                'data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                false,
                20,
                true
            ],
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
        $picturefillMarkup,
        $placeholderSize,
        $placeholderInline
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
            true,
            'svg',
            $placeholderSize,
            $placeholderInline
        );
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($dataSrcAttribute, $tag->getAttribute('data-src'));
        $this->assertEquals($dataSrcsetAttribute, $tag->getAttribute('data-srcset'));
    }
}
