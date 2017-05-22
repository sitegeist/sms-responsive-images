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

tx_smsresponsiveimages_demo = PAGE
tx_smsresponsiveimages_demo {
  typeNum = 92387

  meta.viewport = width=device-width, initial-scale=1.0
  includeCSS.demo = EXT:sms_responsive_images/Resources/Public/Css/tx_smsresponsiveimages_demo.css
  includeJSFooterlibs.picturefill = EXT:sms_responsive_images/Resources/Public/JavaScript/picturefill.min.js

  10 = COA
  10 {
    wrap = <div class="container">|</div>

    10 = USER
    10 {
      userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
      extensionName = SmsResponsiveImages
      vendorName = SMS
      pluginName = ResponsiveImages

      controller = Media
      action = demo
      switchableControllerActions.Media.1 = demo

      settings =< plugin.tx_smsresponsiveimages.settings
      persistence =< plugin.tx_smsresponsiveimages.persistence
      view =< plugin.tx_smsresponsiveimages.view
    }
  }
}
