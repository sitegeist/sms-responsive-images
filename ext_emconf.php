<?php

########################################################################
# Extension Manager/Repository config file for ext "sms_responsive_images".
#
# Auto generated 05-05-2014 09:20
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
    'title' => 'SMS Responsive Images',
    'description' => 'Provides ViewHelpers and configuration to render valid responsive images based on TYPO3\'s image cropping tool.',
    'category' => 'fe',
    'author' => 'Simon Praetorius',
    'author_email' => 'praetorius@sitegeist.de',
    'author_company' => 'sitegeist media solutions GmbH',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => false,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => false,
    'lockType' => '',
    'version' => '1.3.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '8.7.0-9.9.99',
            'fluid' => '',
            'php' => '7.0.0-0.0.0'
        ),
        'conflicts' => array(
            'fluid_styled_responsive_images' => ''
        ),
        'suggests' => array(
            'fluid_styled_content' => ''
        ),
    )
);
