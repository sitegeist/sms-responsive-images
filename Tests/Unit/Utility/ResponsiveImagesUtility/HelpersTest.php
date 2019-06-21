<?php

namespace SMS\SmsResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;

class HelpersTest extends AbstractResponsiveImagesUtilityTest
{
    public function addMetadataToImageTagWithFocusAreaProvider()
    {
        $imageTagWithAttribute = new TagBuilder('img');
        $imageTagWithAttribute->addAttribute('data-focus-area', 'fixed');

        return [
            // Test focus area attribute
            'usingFocusArea' => [
                new TagBuilder('img'),
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new Area(0.4, 0.4, 0.6, 0.6),
                htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600]))
            ],
            // Test fallback to fixed value
            'usingFixedFocusAreaValue' => [
                $imageTagWithAttribute,
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new Area(0.4, 0.4, 0.6, 0.6),
                'fixed'
            ],
            // Test omitted parameter
            'withoutFocusArea' => [
                new TagBuilder('img'),
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                null,
                null
            ],
            // Test empty focus area
            'withEmptyFocusArea' => [
                new TagBuilder('img'),
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                new Area(0, 0, 1, 1),
                null
            ]
        ];
    }

    /**
     * @test
     * @dataProvider addMetadataToImageTagWithFocusAreaProvider
     */
    public function addMetadataToImageTagWithFocusArea($tag, $originalImage, $fallbackImage, $focusArea, $dataAttribute)
    {
        $this->utility->addMetadataToImageTag($tag, $originalImage, $fallbackImage, $focusArea);
        $this->assertEquals(
            $dataAttribute,
            $tag->getAttribute('data-focus-area')
        );
    }

    public function addMetadataToImageTagWithAltAndTitleProvider()
    {
        $imageTagWithAttributes = new TagBuilder('img');
        $imageTagWithAttributes->addAttribute('alt', 'fixed alt');
        $imageTagWithAttributes->addAttribute('title', 'fixed title');

        return [
            // Test alt and title attributes
            'usingAltAndTitle' => [
                new TagBuilder('img'),
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                'image alt',
                'image title'
            ],
            // Test fixed alt/title attributes
            'usingFixedAltAndTitleValues' => [
                $imageTagWithAttributes,
                $this->mockFileObject(
                    ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg']
                ),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                'fixed alt',
                'fixed title'
            ],
            // Test default alt/title attributes
            'withoutAltAndTitle' => [
                new TagBuilder('img'),
                $this->mockFileObject(['width' => 2000, 'height' => 2000, 'extension' => 'jpg']),
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                '',
                null
            ]
        ];
    }

    /**
     * @test
     * @dataProvider addMetadataToImageTagWithAltAndTitleProvider
     */
    public function addMetadataToImageTagWithAltAndTitle(
        $tag,
        $originalImage,
        $fallbackImage,
        $altAttribute,
        $titleAttribute
    ) {
        $this->utility->addMetadataToImageTag($tag, $originalImage, $fallbackImage);
        $this->assertEquals($altAttribute, $tag->getAttribute('alt'));
        $this->assertEquals($titleAttribute, $tag->getAttribute('title'));
    }

    public function generatesSrcsetImagesProvider()
    {
        return [
            // Test high dpi image srcset
            'usingHighDpi' => [
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                400,
                0,
                ['1x', '2x'],
                null,
                false,
                ['1x' => '/image-400.jpg', '2x' => '/image-800.jpg']
            ],
            // Test responsive image srcset (widths in integers)
            'usingResponsiveWidths' => [
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                400,
                0,
                [200, 400, 600],
                null,
                false,
                ['200w' => '/image-200.jpg', '400w' => '/image-400.jpg', '600w' => '/image-600.jpg']
            ],
            // Test responsive image srcset (widths as strings)
            'usingResponsiveWidthsAsStrings' => [
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                400,
                400,
                ['200w', '400w', '600w'],
                null,
                false,
                ['200w' => '/image-200.jpg', '400w' => '/image-400.jpg', '600w' => '/image-600.jpg']
            ],
            // Test absolute urls
            'requestingAbsoluteUrls' => [
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                400,
                400,
                ['200w', '400w', '600w'],
                null,
                true,
                [
                    '200w' => 'http://domain.tld/image-200.jpg',
                    '400w' => 'http://domain.tld/image-400.jpg',
                    '600w' => 'http://domain.tld/image-600.jpg'
                ]
            ],
            // Test srcset input as string
            'usingSrcsetString' => [
                $this->mockFileObject(['width' => 1000, 'height' => 1000, 'extension' => 'jpg']),
                400,
                200,
                '200, 400, 600',
                null,
                true,
                [
                    '200w' => 'http://domain.tld/image-200.jpg',
                    '400w' => 'http://domain.tld/image-400.jpg',
                    '600w' => 'http://domain.tld/image-600.jpg'
                ]
            ],
            'usingTooSmallImage' => [
                $this->mockFileObject(['width' => 400, 'height' => 400, 'extension' => 'jpg']),
                400,
                200,
                '200, 300, 500',
                null,
                false,
                [
                    '200w' => '/image-200.jpg',
                    '300w' => '/image-300.jpg',
                    '400w' => '/image-400.jpg'
                ]
            ],
            // Test if special characters are kept in file name
            'usingSpecialCharactersInFileName' => [
                $this->mockFileObject(['name' => 'this/is a/filename@with-/special!charac,ters', 'width' => 400, 'height' => 400, 'extension' => 'jpg']),
                400,
                200,
                [200],
                null,
                false,
                [
                    '200w' => '/this/is a/filename@with-/special!charac,ters-200.jpg'
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider generatesSrcsetImagesProvider
     */
    public function generatesSrcsetImages($originalImage, $width, $height, $srcsetConfig, $cropArea, $absoluteUri, $output)
    {
        $this->assertEquals(
            $output,
            $this->utility->generateSrcsetImages(
                $originalImage,
                $width,
                $height,
                $srcsetConfig,
                $cropArea,
                $absoluteUri
            )
        );
    }

    public function generateSrcsetAttributeProvider()
    {
        return [
            // Test empty srcset
            'usingEmptySrcset' => [
                [],
                ''
            ],
            // Check srcset with single image
            'usingSingleSrcsetItem' => [
                ['1x' => 'image-1x.jpg'],
                'image-1x.jpg 1x'
            ],
            // Check srcset with multiple images
            'usingMultipleSrcsetItems' => [
                ['200w' => 'image-200.jpg', '400w' => 'image-400.jpg', '600w' => 'image-600.jpg'],
                'image-200.jpg 200w, image-400.jpg 400w, image-600.jpg 600w'
            ],
            // Test if special characters are encoded in file name
            'usingSpecialCharactersInFileName' => [
                [
                    '200w' => 'this/is a/filename@with-/special!charac,ters-200.jpg',
                    '400w' => 'this/is a/filename@with-/special!charac,ters-400.jpg'
                ],
                'this/is%20a/filename@with-/special!charac%2Cters-200.jpg 200w, this/is%20a/filename@with-/special!charac%2Cters-400.jpg 400w'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider generateSrcsetAttributeProvider
     */
    public function generateSrcsetAttribute($input, $output)
    {
        $this->assertEquals($output, $this->utility->generateSrcsetAttribute($input));
    }

    public function normalizeImageBreakpointsProvider()
    {
        return [
            // Test without any breakpoints
            'withoutBreakpoints' => [
                [],
                []
            ],
            // Test default value for breakpoints
            'usingEmptyBreakpoint' => [
                [[]],
                [
                    [
                        'cropVariant' => 'default',
                        'media' => '',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => []
                    ]
                ]
            ],
            // Test merge with default value
            'usingOverwrittenBreakpointProperties' => [
                [
                    [
                        'cropVariant' => 'test',
                        'srcset' => [200, 400, 600],
                        'media' => 'overwrittenMedia'
                    ]
                ],
                [
                    [
                        'cropVariant' => 'test',
                        'media' => 'overwrittenMedia',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => [200, 400, 600]
                    ]
                ]
            ],
            // Test sorting by keys (integers)
            'usingUnsortedArrayWithIntegerKeys' => [
                [
                    2 => ['cropVariant' => 'second'],
                    1 => ['cropVariant' => 'first']
                ],
                [
                    1 => [
                        'cropVariant' => 'first',
                        'media' => '',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => []
                    ],
                    2 => [
                        'cropVariant' => 'second',
                        'media' => '',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => []
                    ]
                ]
            ],
            // Test sorting by keys (strings)
            'usingUnsortedArrayWithStringKeys' => [
                [
                    '2' => ['cropVariant' => 'second'],
                    '1' => ['cropVariant' => 'first']
                ],
                [
                    '1' => [
                        'cropVariant' => 'first',
                        'media' => '',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => []
                    ],
                    '2' => [
                        'cropVariant' => 'second',
                        'media' => '',
                        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
                        'srcset' => []
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider normalizeImageBreakpointsProvider
     */
    public function normalizeImageBreakpoints($input, $output)
    {
        $this->assertEquals($output, $this->utility->normalizeImageBreakpoints($input));
    }
}
