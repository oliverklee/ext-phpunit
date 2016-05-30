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
    ],
    'columns' => [
        'hidden' => [
            'config' => [
                'type' => 'check',
            ]
        ],
    ],
];
