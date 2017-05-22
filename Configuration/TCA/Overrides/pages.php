<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sms_responsive_images']);
    if (empty($extConf['enableDemoPlugin'])) {
        return;
    }

    $GLOBALS['TCA']['pages']['columns']['media']['config']['overrideChildTca']['columns']['crop']['config'] = [
        'cropVariants' => [
            'default' => [
                'disabled' => true
            ],
            'mobile' => [
                'title' => 'Mobile',
                'allowedAspectRatios' => [
                    '1:1' => [
                        'title' => 'LLL:EXT:lang/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.1_1',
                        'value' => 1
                    ]
                ],
            ],
            'tablet' => [
                'title' => 'Tablet',
                'allowedAspectRatios' => [
                    '4:3' => [
                        'title' => 'LLL:EXT:lang/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.4_3',
                        'value' => 4 / 3
                    ]
                ],
            ],
            'desktop' => [
                'title' => 'Desktop',
                'allowedAspectRatios' => [
                    '16:9' => [
                        'title' => 'LLL:EXT:lang/Resources/Private/Language/locallang_wizards.xlf:imwizard.ratio.16_9',
                        'value' => 16 / 9
                    ]
                ],
            ]
        ],
    ];
});
