<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'PHPUnit',
    'description' => 'Unit testing for TYPO3. Includes PHPUnit and a CLI test runner.',
    'version' => '6.5.15',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-7.4.99',
            'typo3' => '8.7.0-9.5.99',
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
            'OliverKlee\\PhpUnit\\Tests\\' => 'Tests/'
        ],
    ],
];
