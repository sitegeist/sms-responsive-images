# SMS Responsive Images

## Responsive Images for TYPO3

This TYPO3 extension provides ViewHelpers and configuration to render valid
responsive images based on TYPO3's image cropping tool.

### Authors & Sponsors

* Simon Praetorius - praetorius@sitegeist.de
* [All contributors](https://github.com/sitegeist/sms-responsive-images/graphs/contributors)

*The development and the public-releases of this package is generously sponsored
by my employer https://sitegeist.de.*

## Installation

This extension is available via packagist.

    composer require sitegeist/sms-responsive-images

Alternatively, you can install the extension from TYPO3 TER:

[TYPO3 TER: sms_responsive_images](https://extensions.typo3.org/extension/sms_responsive_images)

For further instructions, please take a look at the full documentation.

## Updating from 2.x

* Support for TYPO3 9.5 and PHP < 7.4 is gone.
* If you still include `constants.ts` and `setup.ts` manually in your TypoScript configuration, these
files have now been renamed to `constants.typoscript` and `setup.typoscript`.

## Updating from 1.x

There are a few breaking changes which might require you to update your integration:

1. Support for TYPO3 8.7 is gone. Please use version 1.3 of the extension.
2. The `picturefill` attribute of `<sms:image />` and `<sms:media />` has been removed,
so you need to remove it from your Fluid templates. Separate markup for picturefill.js
is no longer required, so the extension now outputs standards-compliant markup at any time.
3. In addition to svg files, gif files are now excluded as well. You can change this by
adjusting the `ignoreFileExtensions` parameter.
4. If `lazyload` is enabled, image tags will get a `class="lazyload"` automatically.

There are also some changes under the hood you might want to consider:

1. The PHP namespace has switched from `SMS\SmsResponsiveImages` to `Sitegeist\ResponsiveImages`,
so if you extended one of the provided PHP classes, you need to adjust this.
2. The extension now uses `.1579774724` instead of `.100` to overwrite the image partial
of fluid_styled_content. This means that there will be less interference with other extensions.
However, if you need to overwrite the `Image.html` file again, you need to specify your
partial after that value.
3. The demo plugin is gone, so if you were using it, it won't work anymore.

## Documentation

To get an overview of responsive images in general and what the extension does, take a
look at the following blog post:

[sitegeist Techblog: Responsive Images with TYPO3 8.7+](https://sitegeist.de/blog/typo3-blog/responsive-images-with-typo3-8-7.html)

You will find the full documentation for this extension on typo3.org:

[Full Documentation of SMS Responsive Images](https://docs.typo3.org/p/sitegeist/sms-responsive-images/master/en-us/)
