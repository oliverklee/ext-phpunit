<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'hideTable' => true,
        'adminOnly' => true,
    ],
    'columns' => [
        'hidden' => [
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'config' => [
                'type' => 'input',
            ],
        ],
        'test' => [
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_ccc_test',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'MM' => 'tx_ccc_data_test_mm',
            ],
        ],
    ],
];
