<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_bbb_test'] = array(
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

$tempColumns = array(
	'tx_bbb_test' => array(
		'config' => array(
			'type' => 'input',
		)
	),
);

ExtensionManagementUtility::addTCAcolumns('tx_aaa_test', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('tx_aaa_test', 'tx_bbb_test;;;;1-1-1');