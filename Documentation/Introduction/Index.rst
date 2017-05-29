.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

This extension aims to provide an easy way to add responsive images support to TYPO3 CMS. While responsive image setups can become quite complicated, the basic setup really shouldn't be. The extension provides sensible default values while allowing pro users to overwrite and to extend them.

Responsive Images with HTML5 standards
--------------------------------------

There are several ways in which responsive images can be implemented. This extension sticks to the standard ways defined in the HTML5 standard. Based on the provided configuration, the extension automatically picks the appropriate image setup.

First, there is the basic image tag which TYPO3 generates by default:

::

   <img
      src="image.jpg"
      alt="alternative text"
   />

The first improvement would be to provide separate images for high dpi screens, such as the "Retina" screens on most Apple devices. This is usually called a *high dpi setup*:

::

   <img
      src="image.jpg"
      srcset="
         image@2x.jpg 2x
      "
      alt="alternative text"
   />

Alternatively, one could provide several image sizes to the browser, which then picks the image that fits best in the current environment (viewport size, pixel density, ...). This is usually called a *responsive images setup*:

::

   <img
      src="fallback.jpg"
      srcset="
         image@400.jpg 400w,
         image@600.jpg 600w,
         image@800.jpg 800w,
         image@1000.jpg 1000w
      "
      alt="alternative text"
   />

With the sizes attribute, one can specify how big the image will be in different screen sizes (as defined by the responsive design of the website). In the example, the image would be at most 800 pixels wide, so the 1000 pixels image would never be loaded (except on high dpi screens):

::

   <img
      src="fallback.jpg"
      srcset="
         image@400.jpg 400w,
         image@600.jpg 600w,
         image@800.jpg 800w,
         image@1000.jpg 1000w
      "
      sizes="(min-width: 800px) 800px, 100vw"
      alt="alternative text"
   />

The picture tag is clearly the flagship of all image tags. It provides two additional features the ordinary image tag can't do:

- Art Direction, a way to define varying image crop variants for different viewport sizes (e. g. square image on mobile, wide image on desktop)
- Support and fallbacks for newer image formats (e. g. WebP); note that this is not supported by this extension!

The syntax is a mixture of other HTML5 media tags (audio/video) and the extended image tags shown in the previous examples. The following setup is usually called a *responsive images setup with art direction*:

::

   <picture>
      <!-- wide image at >= 1000px -->
      <source
         srcset="
            wide@1000.jpg 1000w,
            wide@1500.jpg 1500w
         "
         media="(min-width: 1000px)"
      />
      <!-- square image at < 1000px -->
      <source
         srcset="
            square@500.jpg 500w,
            square@1000.jpg 1000w
         "
      />

      <img src="fallback.jpg" alt="alternative text" />
   </picture>
