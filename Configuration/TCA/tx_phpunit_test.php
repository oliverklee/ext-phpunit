<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'delete' => 'deleted',
        'hideTable' => true,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
    ],
    'interface' => [
        'showRecordFieldList' => '',
    ],
    'columns' => [
        'hidden' => [
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'config' => [
                'type' => 'none',
                'renderType' => 'inputDateTime',
                'size' => 8,
                'max' => 20,
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'endtime' => [
            'config' => [
                'type' => 'none',
                'size' => 8,
                'max' => 20,
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
                'range' => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')),
                ],
            ],
        ],
        'title' => [
            'config' => [
                'type' => 'none',
                'size' => 30,
            ],
        ],
        'related_records' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (m:n relation using an m:n table)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_phpunit_test',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
                'MM' => 'tx_phpunit_test_article_mm',
            ],
        ],
        'bidirectional' => [
            'l10n_mode' => 'exclude',
            'label' => 'Related records (m:n relation using an m:n table)',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingleBox',
                'foreign_table' => 'tx_phpunit_test',
                'size' => 4,
                'minitems' => 0,
                'maxitems' => 99,
                'MM' => 'tx_phpunit_test_article_mm',
                'MM_opposite_field' => 'related_records',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => ''],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];
