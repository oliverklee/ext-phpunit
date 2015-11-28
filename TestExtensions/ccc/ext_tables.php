<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['tx_ccc_test'] = array(
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

$GLOBALS['TCA']['tx_ccc_data'] = array(
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