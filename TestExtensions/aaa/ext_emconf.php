<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'AAA',
    'description' => 'A test extension used for running the phpunit unit tests.',
    'version' => '5.3.5',
    'category' => 'example',
    'constraints' => [
        'depends' => [
            'phpunit' => '',
        ],
    ],
    'state' => 'experimental',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
];
