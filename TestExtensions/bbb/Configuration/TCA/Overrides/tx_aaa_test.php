<?php
defined('TYPO3_MODE') or die('Access denied.');

$tempColumns = [
    'tx_bbb_test' => [
        'config' => [
            'type' => 'input',
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_aaa_test', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_aaa_test', 'tx_bbb_test;;;;1-1-1');
