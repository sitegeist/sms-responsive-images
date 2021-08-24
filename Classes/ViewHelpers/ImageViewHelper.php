<?php

namespace Sitegeist\ResponsiveImages\ViewHelpers;

use Sitegeist\ResponsiveImages\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * @var ResponsiveImagesUtility
     */
    protected $responsiveImagesUtility;

    /**
     * @param ResponsiveImagesUtility $responsiveImagesUtility
     */
    public function injectResponsiveImagesUtility(ResponsiveImagesUtility $responsiveImagesUtility)
    {
        $this->responsiveImagesUtility = $responsiveImagesUtility;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
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
    public function render()
    {
        $src = (string)$this->arguments['src'];
        if (($src === '' && is_null($this->arguments['image']))
            || $src !== '' && !is_null($this->arguments['image'])
        ) {
            throw new \TYPO3Fluid\Fluid\Core\Exception(
                'You must either specify a string src or a File object.',
                1517766588 // Original code: 1382284106
            );
        }

        // Fall back to TYPO3 default if no responsive image feature was selected
        if (!$this->arguments['breakpoints'] && !$this->arguments['srcset']) {
            return parent::render();
        }

        // Add loading attribute to tag
        if (in_array($this->arguments['loading'] ?? '', ['lazy', 'eager', 'auto'], true)) {
            $this->tag->addAttribute('loading', $this->arguments['loading']);
        }

        try {
            // Get FAL image object
            $image = $this->getImageService()->getImage(
                $src,
                $this->arguments['image'],
                $this->arguments['treatIdAsReference']
            );

            // Determine cropping settings
            $cropString = $this->arguments['crop'];
            if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
                $cropString = $image->getProperty('crop');
            }
            $cropVariantCollection = CropVariantCollection::create((string)$cropString);

            $cropVariant = $this->arguments['cropVariant'] ?: 'default';
            $cropArea = $cropVariantCollection->getCropArea($cropVariant);
            $focusArea = $cropVariantCollection->getFocusArea($cropVariant);

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
            $fallbackImage = $this->getImageService()->applyProcessingInstructions($image, $processingInstructions);

            if ($this->arguments['breakpoints']) {
                // Generate picture tag
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
                    $this->arguments['placeholderSize'],
                    $this->arguments['placeholderInline'],
                    $this->arguments['fileExtension']
                );
            } else {
                // Generate img tag with srcset
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
                    $this->arguments['placeholderSize'],
                    $this->arguments['placeholderInline'],
                    $this->arguments['fileExtension']
                );
            }
        } catch (ResourceDoesNotExistException $e) {
            // thrown if file does not exist
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
        }

        return $this->tag->render();
    }

    protected function getImageService(): ImageService
    {
        return GeneralUtility::makeInstance(ImageService::class);
    }
}
