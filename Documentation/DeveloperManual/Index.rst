.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Developer Manual
====================

The extension provides two Fluid ViewHelpers which can generate various image markups. They extend the original ``<f:image />`` and ``<f:media />`` ViewHelpers, so they can also be used for non-responsive images.

In addition to the existing properties, both ViewHelpers support the following properties:

srcset
~~~~~~
:aspect:`Variable type`
   Array|String

:aspect:`Description`
   A list (either array or comma-separated string) of additional image sizes that should be generated for the image. Can be either image widths specified in pixels (e. g. 200 or 200w) or pixel density descriptors (e. g. 1x or 2x), but must not be a combination of both.

:aspect:`Default value`
   NULL

:aspect:`Mandatory`
   No

Examples
--------

::

   <sms:image image="{image}" srcset="400, 600, 800, 1000" />
   <sms:image image="{image}" srcset="400w, 600w, 800w, 1000w" />
   <sms:image image="{image}" srcset="1x, 2x" />
   <sms:image image="{image}" srcset="{0: 400, 1: 600, 2: 800, 3: 1000}" />

   <sms:media file="{image}" srcset="400, 600, 800, 1000" />


sizes
~~~~~~
:aspect:`Variable type`
   String

:aspect:`Description`
   Sizes query which tells the browser which of the image sizes should be used in the current environment. ``%1$d`` can be used as a placeholder for the calculated image width.

:aspect:`Default value`
   (min-width: %1$dpx) %1$dpx, 100vw

:aspect:`Mandatory`
   No

Examples
--------

::

   <sms:image image="{image}" sizes="(min-width: 1200px) 600px, (min-width: 900px) 800px, 100vw" />

   <sms:media file="{image}" sizes="(min-width: 1200px) 600px, (min-width: 900px) 800px, 100vw" />


breakpoints
^^^^^^^^^^^
:aspect:`Variable type`
   Array

:aspect:`Description`
   An array of image breakpoints derived from the underlying responsive design. For each breakpoint, the following options can be set:

   - ``cropVariant``: The name of the corresponding cropVariant as defined in the TCA configuration of the FAL field.
   - ``media``: The media query that tells the browser when this breakpoint should be used.
   - ``srcset``: A list of image sizes that should be generated for this image breakpoint.
   - ``sizes``: Sizes query which tells the browser which of the image sizes should be used in the current environment.

   The result will always be a ``<picture>`` tag. The provided array will be sorted by keys, so it is possible to control the order of the generated ``<source>`` tags.

:aspect:`Default value`
   NULL

:aspect:`Mandatory`
   No

Examples
--------

::

   <sms:image image="{image}" breakpoints="{
      0: {'cropVariant': 'desktop', 'media': '(min-width: 1000px)', 'srcset': '1000, 1200, 1400, 1600'},
      1: {'cropVariant': 'mobile', 'srcset': '400, 600, 800, 1000, 1200, 1400, 1600'}
   }" />

   <sms:media file="{image}" breakpoints="{
      0: {'cropVariant': 'desktop', 'media': '(min-width: 1000px)', 'srcset': '1000, 1200, 1400, 1600'},
      1: {'cropVariant': 'mobile', 'srcset': '400, 600, 800, 1000, 1200, 1400, 1600'}
   } />


picturefill
^^^^^^^^^^^
:aspect:`Variable type`
   Boolean

:aspect:`Description`
   If set to FALSE, the ViewHelper will generate standard-compliant markup instead of the recommended markup by `picturefill <https://scottjehl.github.io/picturefill/>`__.

:aspect:`Default value`
   TRUE

:aspect:`Mandatory`
   No

Examples
--------

::

   <sms:image image="{image}" srcset="400, 600" picturefill="false" />

   <sms:media file="{image}" srcset="400, 600" picturefill="false" />


lazyload
^^^^^^^^
:aspect:`Variable type`
   Boolean

:aspect:`Description`
   If set to TRUE, the ViewHelper will generate markup which allows lazyloading of images with a JavaScript library of your choice.

   - ``src="..."`` will become ``data-src="..."``
   - ``srcset="..."`` will become ``data-srcset="..."``

:aspect:`Default value`
   FALSE

:aspect:`Mandatory`
   No

Examples
--------

::

   <sms:image image="{image}" srcset="400, 600" lazyload="true" />

   <sms:media file="{image}" srcset="400, 600" lazyload="true" />


ignoreFileExtensions
^^^^^^^^^^^^^^^^^^^^
:aspect:`Variable type`
   Array|String

:aspect:`Description`
   List of file extensions for which no responsive images should be generated (e. g. vector images that can't be cropped easily and don't need individual scaling).

:aspect:`Default value`
   'svg'

:aspect:`Mandatory`
   No
