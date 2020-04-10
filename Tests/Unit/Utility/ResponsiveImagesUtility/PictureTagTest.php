<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariant;

class PictureTagTest extends AbstractResponsiveImagesUtilityTest
{
    public function createPictureTagProvider()
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);

        return [
            // Test two breakpoints with media queries with standard output
            'usingTwoBreakpointsWithMediaRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    [
                        'cropVariant' => 'mobile',
                        'srcset' => [400, 800],
                        'media' => 'media mobile',
                        'sizes' => 'sizes mobile'
                    ]
                ],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<source srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source srcset="/image-400.jpg 400w, /image-800.jpg 800w" media="media mobile" sizes="sizes mobile" />',
                    '<img src="/image-1000.jpg" width="1000" alt="" />'
                ]
            ],
            // Test two breakpoints, last one without media query, with standard output
            'usingTwoBreakpointsLastWithoutMediaRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<source srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" />',
                    '<img src="/image-1000.jpg" width="1000" alt="" />'
                ]
            ],
            // Test focus area
            'usingFocusArea' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [],
                $cropVariantCollection,
                new Area(0.4, 0.4, 0.6, 0.6),
                false,
                0,
                false,
                'picture',
                [
                    '<img src="/image-1000.jpg" width="1000" data-focus-area="'
                        . htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600]))
                        . '" alt="" />'
                ]
            ],
            // Test image metadata attributes
            'usingMetadata' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<img src="/image-1000.jpg" width="1000" alt="image alt" title="image title" />'
                ]
            ],
            // Test lazyload markup with standard output
            'usingLazyloadRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                0,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" width="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with placeholder
            'usingLazyloadWithPlaceholder' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" />',
                    '<img src="/image-20.jpg" class="lazyload" width="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with inline placeholder
            'usingLazyloadWithPlaceholder' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                true,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" src="data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=" width="1000" alt="" />'
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider createPictureTagProvider
     */
    public function createPictureTag(
        $originalImage,
        $fallbackImage,
        $breakpoints,
        $cropVariantCollection,
        $focusArea,
        $lazyload,
        $placeholderSize,
        $placeholderInline,
        $tagName,
        $tagContent
    ) {
        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            $breakpoints,
            $cropVariantCollection,
            $focusArea,
            null,
            null,
            false,
            $lazyload,
            'svg',
            $placeholderSize,
            $placeholderInline
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }

    public function createPictureTagWithCustomTagProvider()
    {
        $pictureTag = new TagBuilder('picture-custom');
        $pictureTag->addAttribute('test', 'test attribute');

        return [
            // Test if tag attributes persist
            'usingCustomTag' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new CropVariantCollection([]),
                $pictureTag,
                'picture-custom',
                'test attribute'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPictureTagWithCustomTagProvider
     */
    public function createPictureTagWithCustomTag(
        $originalImage,
        $fallbackImage,
        $cropVariantCollection,
        $pictureTag,
        $tagName,
        $testAttribute
    ) {
        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            $cropVariantCollection,
            null,
            $pictureTag
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($testAttribute, $tag->getAttribute('test'));
    }

    public function createPictureTagWithCustomFallbackTagProvider()
    {
        $fallbackTag = new TagBuilder('img');
        $fallbackTag->addAttribute('alt', 'fixed alt');
        $fallbackTag->addAttribute('title', 'fixed title');
        $fallbackTag->addAttribute('longdesc', 'fixed longdesc');
        $fallbackTag->addAttribute('class', 'myClass');

        return [
            // Test if fallback tag attributes persist
            'usingCustomFallbackTag' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new CropVariantCollection([]),
                clone $fallbackTag,
                false,
                ['<img alt="fixed alt" title="fixed title" longdesc="fixed longdesc" class="myClass" src="/image-1000.jpg" width="1000" />']
            ],
            // Test if fallback tag works with lazyloading
            'usingCustomFallbackTagWithLazyload' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new CropVariantCollection([]),
                clone $fallbackTag,
                true,
                ['<img alt="fixed alt" title="fixed title" longdesc="fixed longdesc" class="myClass lazyload" data-src="/image-1000.jpg" width="1000" />']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPictureTagWithCustomFallbackTagProvider
     */
    public function createPictureTagWithCustomFallbackTag(
        $originalImage,
        $fallbackImage,
        $cropVariantCollection,
        $fallbackTag,
        $lazyload,
        $tagContent
    ) {
        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            $cropVariantCollection,
            null,
            null,
            $fallbackTag,
            false,
            $lazyload
        );
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }

    public function createPictureTagFromSvgProvider()
    {
        return [
            // Test if fallback tag attributes persist
            'withSvgImage' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'svg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'svg']),
                'img',
                '/image-2000.svg',
                null,
                1000,
                1000
            ]
        ];
    }

    /**
     * @test
     * @dataProvider createPictureTagFromSvgProvider
     */
    public function createPictureTagFromSvg($originalImage, $fallbackImage, $tagName, $srcAttribute, $srcsetAttribute, $heightAttribute, $widthAttribute)
    {
        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            new CropVariantCollection([])
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($widthAttribute, $tag->getAttribute('width'));
        $this->assertEquals($heightAttribute, $tag->getAttribute('height'));
    }
}
