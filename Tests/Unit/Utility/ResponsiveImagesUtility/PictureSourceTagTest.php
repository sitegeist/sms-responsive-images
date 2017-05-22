<?php

namespace SMS\SmsResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

class PictureSourceTagTest extends AbstractResponsiveImagesUtilityTest
{
    public function createPictureSourceTagProvider()
    {
        return [
            // Test empty srcset
            'usingEmptySrcset' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                1000,
                [],
                '',
                '',
                false,
                '',
                null
            ],
            // Test high dpi srcset
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                1000,
                ['1x', '2x'],
                '',
                '',
                false,
                '/image@1000.jpg 1x, /image@2000.jpg 2x',
                null
            ],
            // Test responsive images srcset
            'usingResponsiveWidths' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                1000,
                [400, 800],
                '',
                '',
                false,
                '/image@400.jpg 400w, /image@800.jpg 800w',
                null
            ],
            // Test dynamic sizes query
            'usingDynamicSizesQuery' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                1000,
                [400],
                'media query',
                '%d',
                false,
                '/image@400.jpg 400w',
                1000
            ],
            // Test absolute urls
            'requestingAbsoluteUrls' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                1000,
                ['1x', '2x'],
                '',
                '',
                true,
                'http://domain.tld/image@1000.jpg 1x, http://domain.tld/image@2000.jpg 2x',
                null
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createPictureSourceTagProvider
     */
    public function createPictureSourceTag($image, $defaultWidth, $srcset, $mediaQuery, $sizesQuery, $absoluteUri, $srcsetAttribute, $sizesAttribute)
    {
        $tag = $this->utility->createPictureSourceTag($image, $defaultWidth, $srcset, $mediaQuery, $sizesQuery, null, $absoluteUri);
        $this->assertEquals('source', $tag->getTagName());
        $this->assertEquals($mediaQuery, $tag->getAttribute('media'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($sizesAttribute, $tag->getAttribute('sizes'));
    }
}
