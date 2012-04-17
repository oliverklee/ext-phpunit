<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_ccc_test'] = array(
	'ctrl' => $TCA['tx_ccc_test']['ctrl'],
	'columns' => array(
		'hidden' => array(
			'config' => array(
				'type'	=> 'check',
			),
		),
	),
);

$TCA['tx_ccc_data'] = array(
	'ctrl' => $TCA['tx_ccc_data']['ctrl'],
	'columns' => array(
		'hidden' => array(
			'config' => array(
				'type'	=> 'check',
			),
		),
		'title' => array(
			'config' => array(
				'type' => 'input',
			),
		),
		'test' => array(
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_ccc_test',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'MM' => 'tx_ccc_data_test_mm',
			),
		),
	),
);
?>