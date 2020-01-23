<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'SMS Responsive Images',
    'description' => 'Provides ViewHelpers and configuration to render valid responsive images based on TYPO3\'s image cropping tool.',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'state' => 'beta',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'version' => '2.0.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.99-10.9.99',
            'php' => '7.2.0-7.9.99'
        ],
        'conflicts' => [
            'fluid_styled_responsive_images' => ''
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
