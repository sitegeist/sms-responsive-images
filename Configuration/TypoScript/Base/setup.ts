lib.contentElement {
  # Overwrite certain templates from fluid_styled_content
  templateRootPaths.100 = EXT:sms_responsive_images/Resources/Private/Extensions/fluid_styled_content/Templates/
  partialRootPaths.100 = EXT:sms_responsive_images/Resources/Private/Extensions/fluid_styled_content/Partials/
  layoutRootPaths.100 = EXT:sms_responsive_images/Resources/Private/Extensions/fluid_styled_content/Layouts/

  # Add responsive image settings to all content elements
  settings.tx_smsresponsiveimages {
    lazyload = {$tx_smsresponsiveimages.lazyload}
    srcset = {$tx_smsresponsiveimages.srcset}
    sizes = {$tx_smsresponsiveimages.sizes}
    breakpoints {
    }

    # Use picturefill markup
    picturefill = 1

    # Additional css classes for all image elements
    class =
  }
}
