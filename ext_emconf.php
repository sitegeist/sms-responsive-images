<?php
$EM_CONF['sms_responsive_images'] = [
    'title' => 'SMS Responsive Images',
    'description' => 'Provides ViewHelpers and configuration to render valid responsive images based on TYPO3\'s image cropping tool.',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-13.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'fluid_styled_content' => ''
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Sitegeist\\ResponsiveImages\\' => 'Classes'
        ]
    ],
];
