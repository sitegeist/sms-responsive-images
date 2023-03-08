<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

class ImageTagTest extends AbstractResponsiveImagesUtilityTestCase
{
    public static function createSimpleImageTagProvider()
    {
        $predefinedTag = new TagBuilder('img-test');
        $predefinedTag->addAttribute('class', 'myClass');

        return [
            'simple' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                false,
                0,
                false,
                null,
                'img',
                '/image-2000.jpg',
                null,
                null,
                null,
                null,
                null
            ],
            'usingMetadata' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                false,
                0,
                false,
                null,
                'img',
                '/image-2000.jpg',
                null,
                null,
                'image alt',
                'image title',
                null
            ],
            'usingPredefinedTag' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                clone $predefinedTag,
                null,
                false,
                false,
                0,
                false,
                null,
                'img-test',
                '/image-2000.jpg',
                null,
                null,
                null,
                null,
                'myClass'
            ],
            'usingFocusArea' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                new Area(0.4, 0.4, 0.6, 0.6),
                false,
                false,
                0,
                false,
                null,
                'img',
                '/image-2000.jpg',
                null,
                htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600])),
                null,
                null,
                null
            ],
            'usingAbsoluteUri' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                true,
                false,
                0,
                false,
                null,
                'img',
                'http://domain.tld/image-2000.jpg',
                null,
                null,
                null,
                null,
                null
            ],
            'usingLazyload' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                true,
                0,
                false,
                null,
                'img',
                null,
                '/image-2000.jpg',
                null,
                null,
                null,
                'lazyload'
            ],
            'usingLazyloadWithPredefinedTag' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                clone $predefinedTag,
                null,
                false,
                true,
                0,
                false,
                null,
                'img-test',
                null,
                '/image-2000.jpg',
                null,
                null,
                null,
                'myClass lazyload'
            ],
            'usingLazyloadWithPlaceholder' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                true,
                20,
                false,
                null,
                'img',
                '/image-20.jpg',
                '/image-2000.jpg',
                null,
                null,
                null,
                'lazyload'
            ],
            'usingLazyloadWithPlaceholderInline' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                true,
                20,
                true,
                null,
                'img',
                'data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                '/image-2000.jpg',
                null,
                null,
                null,
                'lazyload'
            ],
            'usingCustomFileExtension' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                null,
                null,
                false,
                true,
                20,
                true,
                'webp',
                'img',
                'data:image/webp;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                '/image-2000.webp',
                null,
                null,
                null,
                'lazyload'
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
        $fileExtension,
        $tagName,
        $srcAttribute,
        $dataSrcAttribute,
        $dataFocusAreaAttribute,
        $altAttribute,
        $titleAttribute,
        $classAttribute
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createSimpleImageTag(
            $originalImage,
            $fallbackImage,
            $tag,
            $focusArea,
            $absoluteUri,
            $lazyload,
            $placeholderSize,
            $placeholderInline,
            $fileExtension
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($dataSrcAttribute, $tag->getAttribute('data-src'));
        $this->assertEquals($dataFocusAreaAttribute, $tag->getAttribute('data-focus-area'));
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($titleAttribute, $tag->getAttribute('title'));
        $this->assertEquals($classAttribute, $tag->getAttribute('class'));
    }

    public static function createImageTagWithSrcsetUsingEmptySrcsetProvider()
    {
        return [
            // Test plain tag
            'usingEmptySrcset' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [],
                'img',
                1000,
                1000,
                '/image-1000.jpg',
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
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, $srcsetConfig);
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($widthAttribute, $tag->getAttribute('width'));
        $this->assertEquals($heightAttribute, $tag->getAttribute('height'));
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
    }

    public static function createImageTagWithSrcsetProvider()
    {
        return [
            // Test standard output
            'usingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w'
            ],
            // Test srcset with 3 widths, one having same width as fallback
            'usingThreeWidthsWithFallbackDuplicate' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400, 800, 1000],
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w'
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400, 800],
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w'
            ],
            // Test high dpi
            'usingHighDpi' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                ['1x', '2x'],
                '/image-1000.jpg',
                '/image-1000.jpg 1x, /image-2000.jpg 2x'
            ],
            // Test svg image
            'usingSvg' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
                [100, 200, 300],
                '/image-2000.svg',
                null
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
        $srcsetAttribute
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig
        );
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
    }

    public static function createImageTagWithSrcsetAndFocusAreaProvider()
    {
        return [
            'usingFocusArea' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
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
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        // Test focus area attribute
        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, [], null, $focusArea);
        $this->assertEquals($focusAreaAttribute, $tag->getAttribute('data-focus-area'));
    }

    public static function createImageTagWithSrcsetAndSizesProvider()
    {
        return [
            // Test sizes attribute
            'usingStaticQuery' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                'sizes query',
                'sizes query'
            ],
            // Test sizes attribute with dynamic width
            'usingDynamicQuery' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                '%1$d',
                1000
            ],
            // Test sizes attribute for high dpi setup
            'usingHighDpi' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
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
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

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

    public static function createImageTagWithSrcsetAndMetadataProvider()
    {
        return [
            // Test image metadata attributes
            'usingMetadata' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                'image alt',
                'image title'
            ],
            // Test svg image metadata attributes
            'usingMetadataWithSvg' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
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
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        // Test image metadata attributes
        $tag = $this->utility->createImageTagWithSrcset($originalImage, $fallbackImage, []);
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($titleAttribute, $tag->getAttribute('title'));
    }

    public static function createImageTagWithSrcsetBasedOnCustomTagProvider()
    {
        // Test if original tag attributes persist
        $customTag = new TagBuilder('img');
        $customTag->addAttribute('alt', 'fixed alt');
        $customTag->addAttribute('longdesc', 'long description');

        return [
            'usingCustomTag' => [
                ['width' => 2000, 'height' => 2000, 'alt' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                $customTag,
                'fixed alt',
                'long description'
            ],
            'usingCustomTagWithSvg' => [
                ['width' => 2000, 'height' => 2000, 'alt' => 'image alt', 'title' => 'image title', 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
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
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

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

    public static function createImageTagWithSrcsetAndLazyloadProvider()
    {
        return [
            // Test standard output
            'usingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                null,
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                0,
                false
            ],
            // Test srcset with 2 widths + fallback image
            'usingTwoWidthsWithoutFallbackDuplicate' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400, 800],
                null,
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-800.jpg 800w, /image-1000.jpg 1000w',
                0,
                false
            ],
            // Test svg image
            'withSvgImage' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
                [400, 800],
                null,
                null,
                '/image-2000.svg',
                null,
                0,
                false
            ],
            // Test placeholder image
            'usingPlaceholderImage' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                '/image-20.jpg',
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
                20,
                false
            ],
            // Test placeholder image inline
            'usingPlaceholderImageInline' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                [400],
                'data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                null,
                '/image-1000.jpg',
                '/image-400.jpg 400w, /image-1000.jpg 1000w',
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
        $placeholderSize,
        $placeholderInline
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig,
            null,
            null,
            null,
            null,
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
        $this->assertEquals('lazyload', $tag->getAttribute('class'));
    }

    public static function createImageTagWithSrcsetAndCustomFileExtensionProvider()
    {
        return [
            // Test standard output
            'usingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                false,
                'svg',
                '/image-1000.jpg',
                '/image-400.webp 400w, /image-1000.jpg 1000w',
                null,
                null,
                0,
                false
            ],
            // Test standard output
            'usingLazyload' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                true,
                'svg',
                null,
                null,
                '/image-1000.jpg',
                '/image-400.webp 400w, /image-1000.jpg 1000w',
                0,
                false
            ],
            // Test svg image
            'withSvgImage' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
                [400, 800],
                true,
                'svg',
                null,
                null,
                '/image-1000.svg',
                '/image-400.webp 400w, /image-800.webp 800w, /image-1000.svg 1000w',
                0,
                false
            ],
            // Test with ignored extension
            'withIgnoredExtension' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
                [400, 800],
                false,
                'svg, webp',
                '/image-2000.webp',
                null,
                null,
                null,
                0,
                false
            ],
            // Test placeholder image
            'usingPlaceholderImage' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [400],
                true,
                'svg',
                '/image-20.webp',
                null,
                '/image-1000.jpg',
                '/image-400.webp 400w, /image-1000.jpg 1000w',
                20,
                false
            ],
            // Test placeholder image inline
            'usingPlaceholderImageInline' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                [400],
                true,
                'svg',
                'data:image/webp;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=',
                null,
                '/image-1000.jpg',
                '/image-400.webp 400w, /image-1000.jpg 1000w',
                20,
                true
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createImageTagWithSrcsetAndCustomFileExtensionProvider
     */
    public function createImageTagWithSrcsetAndCustomFileExtension(
        $originalImage,
        $fallbackImage,
        $srcsetConfig,
        $lazyload,
        $ignoreFileExtensions,
        $srcAttribute,
        $srcsetAttribute,
        $dataSrcAttribute,
        $dataSrcsetAttribute,
        $placeholderSize,
        $placeholderInline
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createImageTagWithSrcset(
            $originalImage,
            $fallbackImage,
            $srcsetConfig,
            null,
            null,
            null,
            null,
            false,
            $lazyload,
            $ignoreFileExtensions,
            $placeholderSize,
            $placeholderInline,
            'webp'
        );
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($dataSrcAttribute, $tag->getAttribute('data-src'));
        $this->assertEquals($dataSrcsetAttribute, $tag->getAttribute('data-srcset'));
    }
}
