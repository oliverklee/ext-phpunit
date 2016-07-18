<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'title' => 'LLL:EXT:phpunit/Resource/Private/Language/locallang_backend.xlf:tx_phpunit_test',
        'readOnly' => 1,
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => false,
        'default_sortby' => 'ORDER BY uid',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:phpunit/ext_icon.png',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,starttime,endtime,title,related_records',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'none',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'none',
                'size' => '8',
                'max' => '20',
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
            'exclude' => 0,
            'label' => 'LLL:EXT:phpunit/Resource/Private/Language/locallang_backend.xlf:tx_phpunit_test.title',
            'config' => [
                'type' => 'none',
                'size' => '30',
            ],
        ],
        'related_records' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
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
            'exclude' => 1,
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
        '0' => ['showitem' => 'title, related_records'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime, endtime'],
    ],
];
