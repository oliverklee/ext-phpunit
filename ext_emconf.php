<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'PHPUnit',
    'description' => 'Unit testing for TYPO3. Includes PHPUnit and a CLI test runner.',
    'version' => '5.7.27',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.2.99',
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'uploadfolder' => true,
    'createDirs' => '',
    'clearCacheOnLoad' => false,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'classmap' => [
            'Classes',
            'Tests',
        ],
    ],
];
