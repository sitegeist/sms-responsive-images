<?php

declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\ViewHelpers;

use Sitegeist\ResponsiveImages\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

final class MediaViewHelper extends AbstractTagBasedViewHelper
{
    protected $tagName = 'img';
    protected ImageService $imageService;
    protected ResponsiveImagesUtility $responsiveImagesUtility;

    public function injectImageService(ImageService $imageService): void
    {
        $this->imageService = $imageService;
    }

    public function injectResponsiveImagesUtility(ResponsiveImagesUtility $responsiveImagesUtility): void
    {
        $this->responsiveImagesUtility = $responsiveImagesUtility;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        // phpcs:disable Generic.Files.LineLength
        $this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', false);
        $this->registerArgument('file', 'object', 'File', true);
        $this->registerArgument('additionalConfig', 'array', 'This array can hold additional configuration that is passed though to the Renderer object', false, []);
        $this->registerArgument('width', 'string', 'This can be a numeric value representing the fixed width of in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'This can be a numeric value representing the fixed height in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');
        $this->registerArgument('fileExtension', 'string', 'Custom file extension to use for images');
        $this->registerArgument('loading', 'string', 'Native lazy-loading for images property. Can be "lazy", "eager" or "auto". Used on image files only.');
        $this->registerArgument('decoding', 'string', 'Provides an image decoding hint to the browser. Can be "sync", "async" or "auto"', false);
        // phpcs:enable

        $this->registerArgument('srcset', 'mixed', 'Image sizes that should be rendered.', false);
        $this->registerArgument(
            'sizes',
            'string',
            'Sizes query for responsive image.',
            false,
            '(min-width: %1$dpx) %1$dpx, 100vw'
        );
        $this->registerArgument('breakpoints', 'array', 'Image breakpoints from responsive design.', false);
        $this->registerArgument('lazyload', 'bool', 'Generate markup that supports lazyloading', false, false);
        $this->registerArgument(
            'placeholderSize',
            'int',
            'Size of the placeholder image for lazyloading (0 = disabled)',
            false,
            0
        );
        $this->registerArgument(
            'placeholderInline',
            'bool',
            'Embed placeholder image for lazyloading inline as data uri',
            false,
            false
        );
        $this->registerArgument(
            'ignoreFileExtensions',
            'mixed',
            'File extensions that won\'t generate responsive images',
            false,
            'svg, gif'
        );
    }

    /**
     * Render a given media file.
     *
     * @throws \UnexpectedValueException
     * @throws Exception
     */
    public function render(): string
    {
        $file = $this->arguments['file'];
        $additionalConfig = (array)$this->arguments['additionalConfig'];
        $width = $this->arguments['width'];
        $height = $this->arguments['height'];

        // get Resource Object (non ExtBase version)
        if (is_callable([$file, 'getOriginalResource'])) {
            // We have a domain model, so we need to fetch the FAL resource object from there
            $file = $file->getOriginalResource();
        }

        if (!$file instanceof FileInterface) {
            throw new \UnexpectedValueException(
                'Supplied file object type ' . get_class($file) . ' must be FileInterface.',
                1678270961 // Original code: 1454252193
            );
        }

        if (!$this->isKnownFileExtension($this->arguments['fileExtension'])) {
            throw new Exception(sprintf(
                'The extension %s is not specified in %s as a valid image file extension and can not be processed.',
                $this->arguments['fileExtension'],
                '$GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
            ), 1631539412); // Original code: 1619030957
        }

        $fileRenderer = GeneralUtility::makeInstance(RendererRegistry::class)->getRenderer($file);

        // Fallback to image when no renderer is found
        if ($fileRenderer === null) {
            if ($this->arguments['breakpoints']) {
                return $this->renderPicture($file, $width, $height, $this->arguments['fileExtension'] ?? null);
            } elseif ($this->arguments['srcset']) {
                return $this->renderImageSrcset($file, $width, $height, $this->arguments['fileExtension'] ?? null);
            } else {
                return $this->renderSimpleImage($file, $width, $height, $this->arguments['fileExtension'] ?? null);
            }
        }
        $additionalConfig = array_merge_recursive($this->arguments, $additionalConfig);
        return $fileRenderer->render($file, $width, $height, $additionalConfig);
    }

    /**
     * Render simple img tag
     *
     * @param string $width
     * @param string $height
     * @return string Rendered img tag
     */
    protected function renderSimpleImage(FileInterface $image, $width, $height, ?string $fileExtension): string
    {
        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $processingInstructions = [
            'width' => $width,
            'height' => $height,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        if (!empty($fileExtension)) {
            $processingInstructions['fileExtension'] = $fileExtension;
        }
        $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
        $imageUri = $this->imageService->getImageUri($processedImage);

        if (!$this->tag->hasAttribute('data-focus-area')) {
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            if (!$focusArea->isEmpty()) {
                $this->tag->addAttribute('data-focus-area', $focusArea->makeAbsoluteBasedOnFile($image));
            }
        }
        $this->tag->addAttribute('src', $imageUri);
        $this->tag->addAttribute('width', $processedImage->getProperty('width'));
        $this->tag->addAttribute('height', $processedImage->getProperty('height'));
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }
        if (in_array($this->arguments['decoding'] ?? '', ['sync', 'async', 'auto'], true)) {
            $this->tag->addAttribute('decoding', $this->arguments['decoding']);
        }

        $alt = $image->getProperty('alternative');
        $title = $image->getProperty('title');

        // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
        if (empty($this->arguments['alt'])) {
            $this->tag->addAttribute('alt', $alt);
        }
        if (empty($this->arguments['title']) && $title) {
            $this->tag->addAttribute('title', $title);
        }

        return $this->tag->render();
    }

    /**
     * Render picture tag
     *
     * @param  FileInterface $image
     * @param  string        $width
     * @param  string        $height
     *
     * @return string                 Rendered picture tag
     */
    protected function renderPicture(FileInterface $image, $width, $height, ?string $fileExtension = null)
    {
        // Get crop variants
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string) $cropString);

        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $focusArea = $cropVariantCollection->getFocusArea($cropVariant);

        // Generate fallback image
        $fallbackImage = $this->generateFallbackImage($image, $width, $cropArea, $fileExtension);

        // Add loading attribute to tag
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }
        if (in_array($this->arguments['decoding'] ?? '', ['sync', 'async', 'auto'], true)) {
            $this->tag->addAttribute('decoding', $this->arguments['decoding']);
        }

        // Generate picture tag
        $this->tag = $this->responsiveImagesUtility->createPictureTag(
            $image,
            $fallbackImage,
            $this->arguments['breakpoints'],
            $cropVariantCollection,
            $focusArea,
            null,
            $this->tag,
            false,
            $this->arguments['lazyload'],
            $this->arguments['ignoreFileExtensions'],
            (int) $this->arguments['placeholderSize'],
            $this->arguments['placeholderInline'],
            $fileExtension
        );

        return $this->tag->render();
    }

    /**
     * Render img tag with srcset/sizes attributes
     *
     * @param  FileInterface $image
     * @param  string        $width
     * @param  string        $height
     *
     * @return string                 Rendered img tag
     */
    protected function renderImageSrcset(FileInterface $image, $width, $height, ?string $fileExtension = null)
    {
        // Get crop variants
        $cropString = $image instanceof FileReference ? $image->getProperty('crop') : '';
        $cropVariantCollection = CropVariantCollection::create((string) $cropString);

        $cropVariant = $this->arguments['cropVariant'] ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);
        $focusArea = $cropVariantCollection->getFocusArea($cropVariant);

        // Generate fallback image
        $fallbackImage = $this->generateFallbackImage($image, $width, $cropArea, $fileExtension);

        // Add loading attribute to tag
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }
        if (in_array($this->arguments['decoding'] ?? '', ['sync', 'async', 'auto'], true)) {
            $this->tag->addAttribute('decoding', $this->arguments['decoding']);
        }

        // Generate image tag
        $this->tag = $this->responsiveImagesUtility->createImageTagWithSrcset(
            $image,
            $fallbackImage,
            $this->arguments['srcset'],
            $cropArea,
            $focusArea,
            $this->arguments['sizes'],
            $this->tag,
            false,
            $this->arguments['lazyload'],
            $this->arguments['ignoreFileExtensions'],
            (int) $this->arguments['placeholderSize'],
            $this->arguments['placeholderInline'],
            $fileExtension
        );

        return $this->tag->render();
    }

    /**
     * Generates a fallback image for picture and srcset markup
     *
     * @param  FileInterface $image
     * @param  string        $width
     * @param  Area          $cropArea
     *
     * @return FileInterface
     */
    protected function generateFallbackImage(
        FileInterface $image,
        $width,
        Area $cropArea,
        ?string $fileExtension = null
    ) {
        $processingInstructions = [
            'width' => $width,
            'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
        ];
        if (!empty($fileExtension)) {
            $processingInstructions['fileExtension'] = $fileExtension;
        }
        $fallbackImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

        return $fallbackImage;
    }

    protected function isKnownFileExtension($fileExtension): bool
    {
        $fileExtension = (string) $fileExtension;
        // Skip if no file extension was specified
        if ($fileExtension === '') {
            return true;
        }
        // Check against list of supported extensions
        return GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $fileExtension);
    }
}
