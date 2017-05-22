<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sms_responsive_images']);
    if (empty($extConf['enableDemoPlugin'])) {
        return;
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SMS.sms_responsive_images',
        'ResponsiveImages',
        ['Media' => 'demo']
    );
});


// $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper'] = [
//     'className' => 'SMS\SmsImageViewhelper\ViewHelpers\ImageViewHelper'
// ];
// $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Fluid\ViewHelpers\MediaViewHelper'] = [
//     'className' => 'SMS\SmsImageViewhelper\ViewHelpers\MediaViewHelper'
// ];
