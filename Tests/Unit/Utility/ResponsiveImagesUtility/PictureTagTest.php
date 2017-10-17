<?php

namespace SMS\SmsResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
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
            // Test two breakpoints with media queries
            'usingTwoBreakpointsWithMedia' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                true,
                false,
                'picture',
                [
                    '<source srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source srcset="/image@400.jpg 400w, /image@800.jpg 800w" media="media mobile" sizes="sizes mobile" />',
                    '<img srcset="/image@1000.jpg" width="1000" alt="" />'
                ]
            ],
            // Test two breakpoints with media queries with standard output
            'usingTwoBreakpointsWithMediaRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                false,
                'picture',
                [
                    '<source srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source srcset="/image@400.jpg 400w, /image@800.jpg 800w" media="media mobile" sizes="sizes mobile" />',
                    '<img src="/image@1000.jpg" width="1000" alt="" />'
                ]
            ],
            // Test two breakpoints, last one without media query
            'usingTwoBreakpointsLastWithoutMedia' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                        'sizes' => 'sizes mobile'
                    ]
                ],
                $cropVariantCollection,
                null,
                true,
                false,
                'picture',
                [
                    '<source srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<img srcset="/image@400.jpg 400w, /image@800.jpg 800w" sizes="sizes mobile" width="1000" alt="" />'
                ]
            ],
            // Test two breakpoints, last one without media query, with standard output
            'usingTwoBreakpointsLastWithoutMediaRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                false,
                'picture',
                [
                    '<source srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source srcset="/image@400.jpg 400w, /image@800.jpg 800w" sizes="sizes mobile" />',
                    '<img src="/image@1000.jpg" width="1000" alt="" />'
                ]
            ],
            // Test focus area
            'usingFocusArea' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [],
                $cropVariantCollection,
                new Area(0.4, 0.4, 0.6, 0.6),
                true,
                false,
                'picture',
                [
                    '<img srcset="/image@1000.jpg" width="1000" data-focus-area="'
                        . htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600]))
                        . '" alt="" />'
                ]
            ],
            // Test image metadata attributes
            'usingMetadata' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                [],
                $cropVariantCollection,
                null,
                true,
                false,
                'picture',
                [
                    '<img srcset="/image@1000.jpg" width="1000" alt="image alt" title="image title" />'
                ]
            ],
            // Test lazyload markup
            'usingLazyload' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                true,
                'picture',
                [
                    '<source data-srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<img data-srcset="/image@400.jpg 400w, /image@800.jpg 800w" sizes="sizes mobile" width="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with standard output
            'usingLazyloadRequestingStandardOutput' => [
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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
                true,
                'picture',
                [
                    '<source data-srcset="/image@500.jpg 500w, /image@1000.jpg 1000w" media="media desktop" sizes="sizes desktop" />',
                    '<source data-srcset="/image@400.jpg 400w, /image@800.jpg 800w" sizes="sizes mobile" />',
                    '<img data-src="/image@1000.jpg" width="1000" alt="" />'
                ]
            ]
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
        $picturefillMarkup,
        $lazyload,
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
            $picturefillMarkup,
            false,
            $lazyload
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
                $this->mockFileObject(['width' => 2000, 'height' => 2000]),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
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

        return [
            // Test if fallback tag attributes persist
            'usingCustomFallbackTag' => [
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000]),
                new CropVariantCollection([]),
                $fallbackTag,
                ['<img alt="fixed alt" title="fixed title" longdesc="fixed longdesc" srcset="/image@1000.jpg" width="1000" />']
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
        $tagContent
    ) {
        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            $cropVariantCollection,
            null,
            null,
            $fallbackTag
        );
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }
}
