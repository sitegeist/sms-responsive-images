<?php
defined('TYPO3_MODE') or die();

// Register base TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'sms_responsive_images',
    'Configuration/TypoScript/Base/',
    'Responsive Images'
);

call_user_func(function () {
    // Check if demo plugin should be enabled
    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sms_responsive_images']);
    if (empty($extConf['enableDemoPlugin'])) {
        return;
    }

    // Enable demo plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'sms_responsive_images',
        'ResponsiveImages',
        'ResponsiveImages'
    );

    // Register static TypoScript for demo page
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'sms_responsive_images',
        'Configuration/TypoScript/Demo/',
        'Responsive Images (Demo)'
    );
});
