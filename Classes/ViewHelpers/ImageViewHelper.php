<?php

declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\ViewHelpers;

use Sitegeist\ResponsiveImages\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

final class ImageViewHelper extends AbstractTagBasedViewHelper
{
    protected $tagName = 'img';
    protected ImageService $imageService;
    protected ResponsiveImagesUtility $responsiveImagesUtility;

    public function __construct()
    {
        parent::__construct();
        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
        $this->responsiveImagesUtility = GeneralUtility::makeInstance(ResponsiveImagesUtility::class);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        // phpcs:disable Generic.Files.LineLength
        $this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', false);
        $this->registerTagAttribute('ismap', 'string', 'Specifies an image as a server-side image-map. Rarely used. Look at usemap instead', false);
        $this->registerTagAttribute('longdesc', 'string', 'Specifies the URL to a document that contains a long description of an image', false);
        $this->registerTagAttribute('usemap', 'string', 'Specifies an image as a client-side image-map', false);
        $this->registerTagAttribute('loading', 'string', 'Native lazy-loading for images property. Can be "lazy", "eager" or "auto"', false);
        $this->registerTagAttribute('decoding', 'string', 'Provides an image decoding hint to the browser. Can be "sync", "async" or "auto"', false);

        $this->registerArgument('src', 'string', 'a path to a file, a combined FAL identifier or an uid (int). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead', false, '');
        $this->registerArgument('treatIdAsReference', 'bool', 'given src argument is a sys_file_reference record', false, false);
        $this->registerArgument('image', 'object', 'a FAL object (\\TYPO3\\CMS\\Core\\Resource\\File or \\TYPO3\\CMS\\Core\\Resource\\FileReference)');
        $this->registerArgument('crop', 'string|bool', 'overrule cropping of image (setting to FALSE disables the cropping set in FileReference)');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');
        $this->registerArgument('fileExtension', 'string', 'Custom file extension to use');

        $this->registerArgument('width', 'string', 'width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('height', 'string', 'height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.');
        $this->registerArgument('minWidth', 'int', 'minimum width of the image');
        $this->registerArgument('minHeight', 'int', 'minimum height of the image');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
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
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws \TYPO3Fluid\Fluid\Core\Exception
     * @return string Rendered tag
     */
    public function render(): string
    {
        $src = (string)$this->arguments['src'];
        if (($src === '' && is_null($this->arguments['image']))
            || $src !== '' && !is_null($this->arguments['image'])
        ) {
            throw new Exception(
                'You must either specify a string src or a File object.',
                1517766588 // Original code: 1382284106
            );
        }

        if (!$this->isKnownFileExtension($this->arguments['fileExtension'])) {
            throw new Exception(sprintf(
                'The extension %s is not specified in %s as a valid image file extension and can not be processed.',
                $this->arguments['fileExtension'],
                '$GLOBALS[\'TYPO3_CONF_VARS\'][\'GFX\'][\'imagefile_ext\']'
            ), 1631539412); // Original code: 1618989190
        }

        // Add loading attribute to tag
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }

        try {
            // Get FAL image object
            $image = $this->imageService->getImage(
                $src,
                $this->arguments['image'],
                (bool) $this->arguments['treatIdAsReference']
            );

            // Determine cropping settings
            $cropString = $this->arguments['crop'];
            if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
                $cropString = $image->getProperty('crop');
            }
            $cropVariantCollection = CropVariantCollection::create((string)$cropString);

            $cropVariant = $this->arguments['cropVariant'] ?: 'default';
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);

            $focusArea = null;
            if (!$this->tag->hasAttribute('data-focus-area')) {
                $focusArea = $cropVariantCollection->getFocusArea($cropVariant);
            }

            // Generate fallback image
            $processingInstructions = [
                'width' => $this->arguments['width'],
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];
            if (!empty($this->arguments['fileExtension'])) {
                $processingInstructions['fileExtension'] = $this->arguments['fileExtension'];
            }
            // Set min/maxWidth only if they are given
            if (!is_null($this->arguments['minWidth'])) {
                $processingInstructions['minWidth'] = $this->arguments['minWidth'];
            }
            if (!is_null($this->arguments['maxWidth'])) {
                $processingInstructions['maxWidth'] = $this->arguments['maxWidth'];
            }

            if ($this->arguments['breakpoints']) {
                // Generate picture tag
                $fallbackImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
                $this->tag = $this->responsiveImagesUtility->createPictureTag(
                    $image,
                    $fallbackImage,
                    $this->arguments['breakpoints'],
                    $cropVariantCollection,
                    $focusArea,
                    null,
                    $this->tag,
                    $this->arguments['absolute'],
                    $this->arguments['lazyload'],
                    $this->arguments['ignoreFileExtensions'],
                    (int) $this->arguments['placeholderSize'],
                    $this->arguments['placeholderInline'],
                    $this->arguments['fileExtension']
                );
            } elseif ($this->arguments['srcset']) {
                // Generate img tag with srcset
                $fallbackImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
                $this->tag = $this->responsiveImagesUtility->createImageTagWithSrcset(
                    $image,
                    $fallbackImage,
                    $this->arguments['srcset'],
                    $cropArea,
                    $focusArea,
                    $this->arguments['sizes'],
                    $this->tag,
                    $this->arguments['absolute'],
                    $this->arguments['lazyload'],
                    $this->arguments['ignoreFileExtensions'],
                    (int) $this->arguments['placeholderSize'],
                    $this->arguments['placeholderInline'],
                    $this->arguments['fileExtension']
                );
            } else {
                // For simple images, height calculation is not a problem and is done the same way
                // the core does it
                $processingInstructions = array_merge($processingInstructions, [
                    'height' => $this->arguments['height'],
                    'minHeight' => $this->arguments['minHeight'],
                    'maxHeight' => $this->arguments['maxHeight']
                ]);
                $fallbackImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

                $this->tag = $this->responsiveImagesUtility->createSimpleImageTag(
                    $fallbackImage,
                    null,
                    $this->tag,
                    $focusArea,
                    $this->arguments['absolute'],
                    $this->arguments['lazyload'],
                    (int) $this->arguments['placeholderSize'],
                    $this->arguments['placeholderInline'],
                    $this->arguments['fileExtension']
                );
            }
        } catch (ResourceDoesNotExistException $e) {
            // thrown if file does not exist
            throw new Exception($e->getMessage(), 1678270145, $e); // Original code: 1509741911
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
            throw new Exception($e->getMessage(), 1678270146, $e); // Original code: 1509741912
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
            throw new Exception($e->getMessage(), 1678270147, $e); // Original code: 1509741913
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
            throw new Exception($e->getMessage(), 1678270148, $e); // Original code: 1509741914
        }

        return $this->tag->render();
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
