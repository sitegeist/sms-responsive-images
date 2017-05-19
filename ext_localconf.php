<?php
defined('TYPO3_MODE') or die();

if ($_EXTCONF['enableDemoPlugin']) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SMS.' . $_EXTKEY,
        'ResponsiveImages',
        ['Media' => 'demo']
    );
}

// $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper'] = [
//     'className' => 'SMS\SmsImageViewhelper\ViewHelpers\ImageViewHelper'
// ];
// $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Fluid\ViewHelpers\MediaViewHelper'] = [
//     'className' => 'SMS\SmsImageViewhelper\ViewHelpers\MediaViewHelper'
// ];
