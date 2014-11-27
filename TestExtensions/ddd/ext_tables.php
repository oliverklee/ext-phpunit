<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_ddd_test'] = array(
	'ctrl' => array(
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'hideTable' => TRUE,
		'dynamicConfigFile' => ExtensionManagementUtility::extPath($_EXTKEY) . 'tca.php',
	),
);