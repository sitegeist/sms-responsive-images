<?php

namespace SMS\SmsResponsiveImages\ViewHelpers;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use SMS\SmsResponsiveImages\Utility\ResponsiveImagesUtility;

class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{
    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('srcset', 'array', 'Image sizes that should be rendered.', false);
        $this->registerArgument('sizes', 'string', 'Sizes query for responsive image.', false, '(min-width: %1$dpx) %1$dpx, 100vw');
        $this->registerArgument('breakpoints', 'array', 'Image breakpoints from responsive design.', false);
        $this->registerArgument('picturefill', 'bool', 'Use rendering suggested by picturefill.js', false, true);
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render()
    {
        if ((is_null($this->arguments['src']) && is_null($this->arguments['image'])) || (!is_null($this->arguments['src']) && !is_null($this->arguments['image']))) {
            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('You must either specify a string src or a File object.', 1382284106);
        }

        // Fall back to TYPO3 default if no responsive image feature was selected
        if (!$this->arguments['breakpoints'] && !$this->arguments['srcset']) {
            return parent::render();
        }

        try {
            // Get FAL image object
            $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);

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
                'minWidth' => $this->arguments['minWidth'],
                'maxWidth' => $this->arguments['maxWidth'],
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];
            $fallbackImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);

            if ($this->arguments['breakpoints']) {
                // Generate picture tag
                $this->tag = $this->getResponsiveImagesUtility()->createPictureTag(
                    $image,
                    $fallbackImage,
                    $this->arguments['breakpoints'],
                    $cropVariantCollection,
                    $focusArea,
                    null,
                    $this->tag,
                    $this->arguments['picturefill'],
                    $this->arguments['absolute']
                );
            } else {
                // Generate img tag with srcset
                $this->tag = $this->getResponsiveImagesUtility()->createImageTagWithSrcset(
                    $image,
                    $fallbackImage,
                    $this->arguments['srcset'],
                    $cropArea,
                    $focusArea,
                    $this->arguments['sizes'],
                    $this->tag,
                    $this->arguments['picturefill'],
                    $this->arguments['absolute']
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

    /**
     * Returns an instance of the responsive images utility
     * This fixes an issue with DI after clearing the cache
     *
     * @return ResponsiveImagesUtility
     */
    protected function getResponsiveImagesUtility()
    {
        return $this->objectManager->get(ResponsiveImagesUtility::class);
    }
}
