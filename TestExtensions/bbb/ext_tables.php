<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['tx_bbb_test'] = array(
    'ctrl' => array(
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'hideTable' => true,
        'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'tca.php',
    ),
);

$tempColumns = array(
    'tx_bbb_test' => array(
        'config' => array(
            'type' => 'input',
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_aaa_test', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_aaa_test', 'tx_bbb_test;;;;1-1-1');
