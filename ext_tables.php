<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_phpunit_test'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:phpunit/Resource/Private/Language/locallang_backend.xml:tx_phpunit_test',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => FALSE,
		'default_sortby' => 'ORDER BY uid',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/TCA.php',
		'iconfile' => ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif',
	)
);

if (TYPO3_MODE === 'BE') {
	ExtensionManagementUtility::addModule('tools', 'txphpunitbeM1', '', ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/BackEnd/');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['PHPUnit'][] = 'Tx_Phpunit_Reports_Status';
}