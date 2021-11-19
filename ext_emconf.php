<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'PHPUnit',
    'description' => 'Unit testing for TYPO3. Includes a CLI test runner.',
    'version' => '8.5.0',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-',
            'typo3' => '9.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'psr-4' => [
            'OliverKlee\\PhpUnit\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\PhpUnit\\Tests\\' => 'Tests/'
        ],
    ],
];
