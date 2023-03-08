<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

class PictureSourceTagTest extends AbstractResponsiveImagesUtilityTestCase
{
    public static function createPictureSourceTagProvider()
    {
        return [
            // Test empty srcset
            'usingEmptySrcset' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                [],
                '',
                '',
                false,
                null,
                '',
                null
            ],
            // Test high dpi srcset
            'usingHighDpi' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                ['1x', '2x'],
                '',
                '',
                false,
                null,
                '/image-1000.jpg 1x, /image-2000.jpg 2x',
                null
            ],
            // Test responsive images srcset
            'usingResponsiveWidths' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                [400, 800],
                '',
                '',
                false,
                null,
                '/image-400.jpg 400w, /image-800.jpg 800w',
                null
            ],
            // Test dynamic sizes query
            'usingDynamicSizesQuery' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                [400],
                'media query',
                '%d',
                false,
                null,
                '/image-400.jpg 400w',
                1000
            ],
            // Test custom file extension
            'usingCustomFileExtension' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                [400],
                'media query',
                '%d',
                false,
                'webp',
                '/image-400.webp 400w',
                1000
            ],
            // Test absolute urls
            'requestingAbsoluteUrls' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                1000,
                ['1x', '2x'],
                '',
                '',
                true,
                null,
                'http://domain.tld/image-1000.jpg 1x, http://domain.tld/image-2000.jpg 2x',
                null
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createPictureSourceTagProvider
     */
    public function createPictureSourceTag(
        $image,
        $defaultWidth,
        $srcset,
        $mediaQuery,
        $sizesQuery,
        $absoluteUri,
        $fileExtension,
        $srcsetAttribute,
        $sizesAttribute
    ) {
        $image = $this->mockFileObject($image);

        $tag = $this->utility->createPictureSourceTag(
            $image,
            $defaultWidth,
            $srcset,
            $mediaQuery,
            $sizesQuery,
            null,
            $absoluteUri,
            false,
            $fileExtension
        );
        $this->assertEquals('source', $tag->getTagName());
        $this->assertEquals($mediaQuery, $tag->getAttribute('media'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($sizesAttribute, $tag->getAttribute('sizes'));
    }
}
