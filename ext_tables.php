<?php
defined('TYPO3_MODE') or die();

// Register base TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'sms_responsive_images',
    'Configuration/TypoScript/Base/',
    'Responsive Images'
);
