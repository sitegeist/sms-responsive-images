# Set image dimensions for demo page
styles.content.textmedia {
  maxW = 1200
  maxWInText = 600
}

tx_smsresponsiveimages {
  # Set additional image sizes for content images on demo page
  srcset = 400, 500, 640, 1000, 1200, 2000, 2400

  # If there's only one column, you could use something like this:
  #sizes = (min-width: 1220px) 1200px, (min-width: 1020px) 1000px, (min-width: 660px) 640px, 100vw
}
