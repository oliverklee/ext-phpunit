<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "phpunit".
 *
 * Auto generated 27-11-2014 01:28
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'PHPUnit',
    'description' => 'Unit testing for TYPO3. Includes PHPUnit 4.8, Selenium, a BE test runner module, a CLI test runner, PhpStorm integration and a testing framework.',
    'category' => 'module',
    'shy' => 0,
    'version' => '4.8.24',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => 'Classes/BackEnd',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => '',
    'doNotLoadInFE' => 1,
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.0.99',
            'typo3' => '6.2.4-8.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
    'autoload' => [
        'classmap' => [
            'Classes',
            'Tests',
        ],
    ],
    '_md5_values_when_last_written' => '',
];
