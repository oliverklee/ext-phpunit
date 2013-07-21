<?php
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
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
	),
);

$tempColumns = array(
	'tx_bbb_test' => array(
		'config' => array(
			'type' => 'input',
		)
	),
);

if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6001000) {
	t3lib_div::loadTCA('tx_aaa_test');
}
t3lib_extMgm::addTCAcolumns('tx_aaa_test', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('tx_aaa_test', 'tx_bbb_test;;;;1-1-1');
?>