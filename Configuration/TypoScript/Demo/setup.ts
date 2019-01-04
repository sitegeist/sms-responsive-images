# Associate model with pages table
plugin.tx_smsresponsiveimages {
  persistence {
    classes {
      SMS\SmsResponsiveImages\Domain\Model\Page {
        mapping {
          tableName = pages
        }
      }
    }
  }
}

# Remove existing page setup
page >

# Create demo page
tx_smsresponsiveimages_demo = PAGE
tx_smsresponsiveimages_demo {
  meta.viewport = width=device-width, initial-scale=1.0
  includeCSS.demo = EXT:sms_responsive_images/Resources/Public/Css/tx_smsresponsiveimages_demo.css
  includeJSFooterlibs.picturefill = EXT:sms_responsive_images/Resources/Public/JavaScript/picturefill.min.js

  10 = FLUIDTEMPLATE
  10 {
    file = EXT:sms_responsive_images/Resources/Private/Templates/Demo.html
    variables {
      # Header image
      header = USER
      header {
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        extensionName = SmsResponsiveImages
        vendorName = SMS
        pluginName = ResponsiveImages

        controller = Media
        action = header
        switchableControllerActions.Media {
            1 = header
            2 = testing
        }

        settings =< plugin.tx_smsresponsiveimages.settings
        persistence =< plugin.tx_smsresponsiveimages.persistence
        view =< plugin.tx_smsresponsiveimages.view
      }

      # Main content
      contentMain = CONTENT
      contentMain {
        table = tt_content
        select {
          orderBy = sorting
          where = colPos=0
          languageField = sys_language_uid
        }
      }

      # Content for left column
      contentLeft = CONTENT
      contentLeft {
        table = tt_content
        select {
          orderBy = sorting
          where = colPos=1
          languageField = sys_language_uid
        }
        # This is where you could modify responsive image parameters for this column
        # renderObj < tt_content
        # renderObj {
        #   image.settings.tx_smsresponsiveimages {
        #     srcset = 300, 450, 600, 750, 900
        #     sizes = (min-width: 300px) 300px, 50vw
        #   }
        # }
      }
    }
  }
}
