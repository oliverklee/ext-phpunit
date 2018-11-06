<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Test extension for tx_phpunit',
    'description' => 'A test extension used for running the phpunit unit tests.',
    'version' => '5.3.5',
    'category' => 'example',
    'constraints' => [
        'depends' => [
            'phpunit' => '',
        ],
    ],
    'state' => 'experimental',
    'author' => 'Niels Pardon',
    'author_email' => 'mail@niels-pardon.de',
];
