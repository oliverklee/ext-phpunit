<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'PHPUnit',
    'description' => 'PHPUnit test runner for TYPO3',
    'version' => '8.5.0',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-',
            'typo3' => '9.5.0-12.4.99',
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
