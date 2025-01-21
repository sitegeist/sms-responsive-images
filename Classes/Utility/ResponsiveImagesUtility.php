<?php

declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\Utility;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResponsiveImagesUtility implements SingletonInterface
{
    /**
     * Image Service
     *
     * @var ImageService
     */
    protected $imageService;

    /**
     * Default media breakpoint configuration
     *
     * @var array
     */
    protected $breakpointPrototype = [
        'cropVariant' => 'default',
        'media' => '',
        'sizes' => '(min-width: %1$dpx) %1$dpx, 100vw',
        'srcset' => []
    ];

    /**
     * @param ImageService $imageService
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Creates an image tag with the provided srcset candidates
     *
     * @param FileInterface $originalImage
     * @param FileInterface $fallbackImage
     * @param array|string  $srcset
     * @param Area          $cropArea
     * @param Area          $focusArea
     * @param string        $sizesQuery
     * @param TagBuilder    $tag
     * @param bool          $absoluteUri
     * @param  bool         $lazyload
     * @param  array|string $ignoreFileExtensions
     * @param  int          $placeholderSize
     * @param  bool         $placeholderInline
     *
     * @return TagBuilder
     */
    public function createImageTagWithSrcset(
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        $srcset,
        ?Area $cropArea = null,
        ?Area $focusArea = null,
        ?string $sizesQuery = null,
        ?TagBuilder $tag = null,
        bool $absoluteUri = false,
        bool $lazyload = false,
        $ignoreFileExtensions = 'svg, gif',
        int $placeholderSize = 0,
        bool $placeholderInline = false,
        ?string $fileExtension = null
    ): TagBuilder {
        $tag = $tag ?: GeneralUtility::makeInstance(TagBuilder::class, 'img');

        // Deal with file formats that can't be cropped separately
        if ($this->hasIgnoredFileExtension($originalImage, $ignoreFileExtensions, $fileExtension)) {
            return $this->createSimpleImageTag(
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
        }

        // Generate fallback image url
        $fallbackImageUri = $this->imageService->getImageUri($fallbackImage, $absoluteUri);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        // if lazyload enabled add data- prefix
        $attributePrefix = $lazyload ? 'data-' : '';

        // Add fallback image
        $tag->addAttribute($attributePrefix . 'src', $fallbackImageUri);

        // Create placeholder image for lazyloading
        if ($lazyload && $placeholderSize) {
            $tag->addAttribute('src', $this->generatePlaceholderImage(
                $originalImage,
                $placeholderSize,
                $cropArea,
                $placeholderInline,
                $absoluteUri,
                $fileExtension
            ));
        }

        // Add lazyload class to image tag
        if ($lazyload) {
            $existingClass = $tag->getAttribute('class');
            $tag->addAttribute('class', $existingClass ? $existingClass . ' lazyload' : 'lazyload');
        }

        // Generate different image sizes for srcset attribute
        $srcsetImages = $this->generateSrcsetImages(
            $originalImage,
            $referenceWidth,
            $srcset,
            $cropArea,
            $absoluteUri,
            $fileExtension
        );
        $srcsetMode = substr(key($srcsetImages) ?? 'w', -1); // x or w

        // Add fallback image to source options
        $fallbackWidthDescriptor = ($srcsetMode == 'x') ? '1x'  : $referenceWidth . 'w';
        $srcsetImages[$fallbackWidthDescriptor] = $fallbackImageUri;

        // Set srcset attribute for image tag
        $tag->addAttribute($attributePrefix . 'srcset', $this->generateSrcsetAttribute($srcsetImages));

        // Add sizes attribute to image tag
        if ($srcsetMode == 'w' && $sizesQuery) {
            $tag->addAttribute('sizes', sprintf($sizesQuery, $referenceWidth));
        }

        // Provide image dimensions to be consistent with TYPO3 core behavior
        $tag->addAttribute('width', $referenceWidth);
        $tag->addAttribute('height', $fallbackImage->getProperty('height'));

        // Add metadata to image tag
        $this->addMetadataToImageTag($tag, $originalImage, $fallbackImage, $focusArea);

        return $tag;
    }

    /**
     * Creates a picture tag with the provided image breakpoints
     *
     * @param  FileInterface         $originalImage
     * @param  FileInterface         $fallbackImage
     * @param  array                 $breakpoints
     * @param  CropVariantCollection $cropVariantCollection
     * @param  Area                  $focusArea
     * @param  TagBuilder            $tag
     * @param  TagBuilder            $fallbackTag
     * @param  bool                  $absoluteUri
     * @param  bool                  $lazyload
     * @param  array|string          $ignoreFileExtensions
     * @param  int                   $placeholderSize
     * @param  bool                  $placeholderInline
     *
     * @return TagBuilder
     */
    public function createPictureTag(
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        array $breakpoints,
        CropVariantCollection $cropVariantCollection,
        ?Area $focusArea = null,
        ?TagBuilder $tag = null,
        ?TagBuilder $fallbackTag = null,
        bool $absoluteUri = false,
        bool $lazyload = false,
        $ignoreFileExtensions = 'svg, gif',
        int $placeholderSize = 0,
        bool $placeholderInline = false,
        ?string $fileExtension = null
    ): TagBuilder {
        $tag = $tag ?: GeneralUtility::makeInstance(TagBuilder::class, 'picture');
        $fallbackTag = $fallbackTag ?: GeneralUtility::makeInstance(TagBuilder::class, 'img');

        // Deal with file formats that can't be cropped separately
        if ($this->hasIgnoredFileExtension($originalImage, $ignoreFileExtensions, $fileExtension)) {
            return $this->createSimpleImageTag(
                $originalImage,
                $fallbackImage,
                $fallbackTag,
                $focusArea,
                $absoluteUri,
                $lazyload,
                $placeholderSize,
                $placeholderInline,
                $fileExtension
            );
        }

        // Normalize breakpoint configuration
        $breakpoints = $this->normalizeImageBreakpoints($breakpoints);

        // Use width of fallback image as reference for relative sizes (1x, 2x...)
        $referenceWidth = $fallbackImage->getProperty('width');

        // if lazyload enabled add data- prefix
        $attributePrefix = $lazyload ? 'data-' : '';

        // Add fallback image source
        $fallbackImageUri = $this->imageService->getImageUri($fallbackImage, $absoluteUri);
        $fallbackTag->addAttribute($attributePrefix . 'src', $fallbackImageUri);

        // Add lazyload class to fallback image tag
        if ($lazyload) {
            $existingClass = $fallbackTag->getAttribute('class');
            $fallbackTag->addAttribute('class', $existingClass ? $existingClass . ' lazyload' : 'lazyload');
        }

        // Create placeholder image for lazyloading
        if ($lazyload && $placeholderSize) {
            $fallbackTag->addAttribute('src', $this->generatePlaceholderImage(
                $originalImage,
                $placeholderSize,
                null,
                $placeholderInline,
                $absoluteUri,
                $fileExtension
            ));
        }

        // Provide image width to be consistent with TYPO3 core behavior
        $fallbackTag->addAttribute('width', $referenceWidth);

        // Add metadata to fallback image
        $this->addMetadataToImageTag($fallbackTag, $originalImage, $fallbackImage, $focusArea);

        // Generate source tags for image breakpoints
        $sourceTags = [];
        foreach ($breakpoints as $breakpoint) {
            $cropArea = $cropVariantCollection->getCropArea($breakpoint['cropVariant']);
            $sourceTag = $this->createPictureSourceTag(
                $originalImage,
                $referenceWidth,
                $breakpoint['srcset'],
                $breakpoint['media'],
                $breakpoint['sizes'],
                $cropArea,
                $absoluteUri,
                $lazyload,
                $fileExtension
            );
            $sourceTags[] = $sourceTag->render();
        }

        // Fill picture tag
        $tag->setContent(
            implode('', $sourceTags) . $fallbackTag->render()
        );

        return $tag;
    }

    /**
     * Creates a source tag that can be used inside of a picture tag
     *
     * @param  FileInterface $originalImage
     * @param  int           $defaultWidth
     * @param  array|string  $srcset
     * @param  string        $mediaQuery
     * @param  string        $sizesQuery
     * @param  Area          $cropArea
     * @param  bool          $absoluteUri
     * @param  bool          $lazyload
     *
     * @return TagBuilder
     */
    public function createPictureSourceTag(
        FileInterface $originalImage,
        int $defaultWidth,
        $srcset,
        string $mediaQuery = '',
        string $sizesQuery = '',
        ?Area $cropArea = null,
        bool $absoluteUri = false,
        bool $lazyload = false,
        ?string $fileExtension = null
    ): TagBuilder {
        $cropArea = $cropArea ?: Area::createEmpty();

        // if lazyload enabled add data- prefix
        $attributePrefix = $lazyload ? 'data-' : '';

        // Generate different image sizes for srcset attribute
        $srcsetImages = $this->generateSrcsetImages(
            $originalImage,
            $defaultWidth,
            $srcset,
            $cropArea,
            $absoluteUri,
            $fileExtension
        );
        $srcsetMode = substr(key($srcsetImages) ?? 'w', -1); // x or w

        // Create source tag for this breakpoint
        $sourceTag = GeneralUtility::makeInstance(TagBuilder::class, 'source');
        $sourceTag->addAttribute($attributePrefix . 'srcset', $this->generateSrcsetAttribute($srcsetImages));
        if ($mediaQuery) {
            $sourceTag->addAttribute('media', $mediaQuery);
        }
        if ($srcsetMode == 'w' && $sizesQuery) {
            $sourceTag->addAttribute('sizes', sprintf($sizesQuery, $defaultWidth));
        }

        return $sourceTag;
    }

    /**
     * Creates a simple image tag
     *
     * @param  FileInterface $image
     * @param  FileInterface $fallbackImage
     * @param  TagBuilder    $tag
     * @param  Area          $focusArea
     * @param  bool          $absoluteUri
     * @param  bool          $lazyload
     * @param  int           $placeholderSize
     * @param  bool          $placeholderInline
     *
     * @return TagBuilder
     */
    public function createSimpleImageTag(
        FileInterface $originalImage,
        ?FileInterface $fallbackImage = null,
        ?TagBuilder $tag = null,
        ?Area $focusArea = null,
        bool $absoluteUri = false,
        bool $lazyload = false,
        int $placeholderSize = 0,
        bool $placeholderInline = false,
        ?string $fileExtension = null
    ): TagBuilder {
        $tag = $tag ?: GeneralUtility::makeInstance(TagBuilder::class, 'img');
        $fallbackImage = ($fallbackImage) ?: $originalImage;

        // if lazyload enabled add data- prefix
        $attributePrefix = $lazyload ? 'data-' : '';

        // Add lazyload class to image tag
        if ($lazyload) {
            $existingClass = $tag->getAttribute('class');
            $tag->addAttribute('class', $existingClass ? $existingClass . ' lazyload' : 'lazyload');
        }

        if (!empty($fileExtension)) {
            $simpleImage = $this->imageService->applyProcessingInstructions($originalImage, [
                'fileExtension' => $fileExtension
            ]);
        } else {
            $simpleImage = $originalImage;
        }

        // Set image source
        $tag->addAttribute($attributePrefix . 'src', $this->imageService->getImageUri($simpleImage, $absoluteUri));

        // Create placeholder image for lazyloading
        if ($lazyload && $placeholderSize) {
            $tag->addAttribute('src', $this->generatePlaceholderImage(
                $simpleImage,
                $placeholderSize,
                null,
                $placeholderInline,
                $absoluteUri,
                $fileExtension
            ));
        }

        // Set image proportions
        $tag->addAttribute('width', $fallbackImage->getProperty('width'));
        $tag->addAttribute('height', $fallbackImage->getProperty('height'));

        // Add metadata to image tag
        $this->addMetadataToImageTag($tag, $originalImage, $fallbackImage, $focusArea);

        return $tag;
    }

    /**
     * Adds metadata to image tag
     *
     * @param TagBuilder    $tag
     * @param FileInterface $originalImage
     * @param FileInterface $fallbackImage
     * @param Area          $focusArea
     *
     * @return void
     */
    public function addMetadataToImageTag(
        TagBuilder $tag,
        FileInterface $originalImage,
        FileInterface $fallbackImage,
        ?Area $focusArea = null
    ) {
        $focusArea = $focusArea ?: Area::createEmpty();

        // Add focus area to image tag
        if (!$tag->hasAttribute('data-focus-area') && !$focusArea->isEmpty()) {
            $tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($fallbackImage));
        }

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        $alt = $originalImage->getProperty('alternative');
        if (!$tag->getAttribute('alt')) {
            $tag->addAttribute('alt', $alt);
        }
        $title = $originalImage->getProperty('title');
        if (!$tag->getAttribute('title') && $title) {
            $tag->addAttribute('title', $title);
        }
    }

    /**
     * Renders different image sizes for use in a srcset attribute
     *
     * Input:
     *   1: $srcset = [200, 400]
     *   2: $srcset = ['200w', '400w']
     *   3: $srcset = ['1x', '2x']
     *   4: $srcset = '200, 400'
     *
     * Output:
     *   1+2+4: ['200w' => 'path/to/image@200w.jpg', '400w' => 'path/to/image@200w.jpg']
     *   3: ['1x' => 'path/to/image@1x.jpg', '2x' => 'path/to/image@2x.jpg']
     *
     * @param  FileInterface  $image
     * @param  int            $defaultWidth
     * @param  array|string   $srcset
     * @param  Area           $cropArea
     * @param  bool           $absoluteUri
     *
     * @return array
     */
    public function generateSrcsetImages(
        FileInterface $image,
        int $defaultWidth,
        $srcset,
        ?Area $cropArea = null,
        bool $absoluteUri = false,
        ?string $fileExtension = null
    ): array {
        $cropArea = $cropArea ?: Area::createEmpty();

        // Convert srcset input to array
        if (!is_array($srcset)) {
            $srcset = GeneralUtility::trimExplode(',', $srcset);
        }

        $images = [];
        foreach ($srcset as $widthDescriptor) {
            $widthDescriptor = (string) $widthDescriptor;
            // Determine image width
            $srcsetMode = substr($widthDescriptor, -1);
            switch ($srcsetMode) {
                case 'x':
                    $candidateWidth = (int) ($defaultWidth * (float) substr($widthDescriptor, 0, -1));
                    break;

                case 'w':
                    $candidateWidth = (int) substr($widthDescriptor, 0, -1);
                    break;

                default:
                    $candidateWidth = (int) $widthDescriptor;
                    $srcsetMode = 'w';
                    $widthDescriptor = $candidateWidth . 'w';
            }

            // Generate image
            $processingInstructions = [
                'width' => $candidateWidth,
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];
            if (!empty($fileExtension)) {
                $processingInstructions['fileExtension'] = $fileExtension;
            }
            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

            // If processed file isn't as wide as it should be ([GFX][processor_allowUpscaling] set to false)
            // then use final width of the image as widthDescriptor if not input case 3 is used
            $processedWidth = $processedImage->getProperty('width');
            if ($srcsetMode === 'w' && $processedWidth !== $candidateWidth) {
                $widthDescriptor = $processedWidth . 'w';
            }

            $images[$widthDescriptor] = $this->imageService->getImageUri($processedImage, $absoluteUri);
        }

        return $images;
    }

    /**
     * Generates a tiny placeholder image for lazyloading
     *
     * @param FileInterface $image
     * @param integer $width
     * @param Area $cropArea
     * @param boolean $inline
     * @param boolean $absoluteUri
     *
     * @return string
     */
    public function generatePlaceholderImage(
        FileInterface $image,
        int $width = 20,
        ?Area $cropArea = null,
        bool $inline = false,
        bool $absoluteUri = false,
        ?string $fileExtension = null
    ): string {
        $cropArea = $cropArea ?: Area::createEmpty();

        $processingInstructions = [
            'width' => $width,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        if (!empty($fileExtension)) {
            $processingInstructions['fileExtension'] = $fileExtension;
        }
        $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

        // Disable inline placeholder if the image is not processed at all
        if ($processedImage->usesOriginalFile()) {
            $inline = false;
        }

        if ($inline) {
            return $this->generateDataUri($processedImage);
        } else {
            return $this->imageService->getImageUri($processedImage, $absoluteUri);
        }
    }

    /**
     * Generates a data URI for the specified image file
     *
     * @param FileInterface $image
     *
     * @return string
     */
    public function generateDataUri(FileInterface $image): string
    {
        return 'data:' . $image->getMimeType() . ';base64,' . base64_encode($image->getContents());
    }

    /**
     * Generates the content for a srcset attribute from an array of image urls
     *
     * Input:
     * [
     *   '200w' => 'path/to/image@200w.jpg',
     *   '400w' => 'path/to/image@400w.jpg'
     * ]
     *
     * Output:
     * 'path/to/image@200w.jpg 200w, path/to/image@400w.jpg 400w'
     *
     * @param  array   $srcsetImages
     *
     * @return string
     */
    public function generateSrcsetAttribute(array $srcsetImages): string
    {
        $srcsetString = [];
        foreach ($srcsetImages as $widthDescriptor => $imageCandidate) {
            $srcsetString[] = $this->sanitizeSrcsetUrl($imageCandidate) . ' ' . $widthDescriptor;
        }
        return implode(', ', $srcsetString);
    }

    /**
     * Ensures that the provided url can be used safely in a srcset attribute
     *
     * @param string $url
     *
     * @return string
     */
    public function sanitizeSrcsetUrl(string $url): string
    {
        return strtr($url, [
            ' ' => '%20',
            ',' => '%2C'
        ]);
    }

    /**
     * Normalizes the provided breakpoints configuration
     *
     * @param  array   $breakpoints
     *
     * @return array
     */
    public function normalizeImageBreakpoints(array $breakpoints): array
    {
        foreach ($breakpoints as &$breakpoint) {
            $breakpoint = array_replace($this->breakpointPrototype, $breakpoint);
        }
        ksort($breakpoints);

        return $breakpoints;
    }

    /**
     * Check if the image has a file format that can't be cropped
     *
     * @param  FileInterface $image
     * @param  array|string  $ignoreFileExtensions
     *
     * @return bool
     */
    public function hasIgnoredFileExtension(
        FileInterface $image,
        $ignoreFileExtensions = 'svg, gif',
        ?string $fileExtension = null
    ) {
        $ignoreFileExtensions = (is_array($ignoreFileExtensions))
            ? $ignoreFileExtensions
            : GeneralUtility::trimExplode(',', $ignoreFileExtensions);

        if (!empty($fileExtension)) {
            return in_array($fileExtension, $ignoreFileExtensions);
        } else {
            return in_array($image->getProperty('extension'), $ignoreFileExtensions);
        }
    }
}
