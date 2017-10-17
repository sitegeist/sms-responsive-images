tx_smsresponsiveimages {
  # cat=content/cTextmedia/b81; type=string; label= Additional Image Sizes for Responsive Images: Additional image sizes that should be generated for each content image (comma-separated list of either image widths specified in pixels or pixel density descriptors, e. g. "2x")
  srcset = 400, 600, 800, 1000, 1200

  # cat=content/cTextmedia/b82; type=string; label= Sizes Query for Responsive Images: Sizes query which tells the browser which of the image sizes should be used in the current environment (%1$d can be used as a placeholder for the calculated image width)
  sizes = (min-width: %1$dpx) %1$dpx, 100vw

  # cat=content/cTextmedia/b83; type=boolean; label= Generate image markup that supports lazyloading
  lazyload = 0
}
