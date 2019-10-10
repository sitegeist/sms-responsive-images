<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    // Make sms a global namespace
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['sms'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['sms'] = [];
    }
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['sms'][] = 'Sitegeist\\ResponsiveImages\\ViewHelpers';
});
