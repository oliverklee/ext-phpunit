<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['tx_ccc_test'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_ccc_test']['ctrl'],
	'columns' => array(
		'hidden' => array(
			'config' => array(
				'type'	=> 'check',
			),
		),
	),
);

$GLOBALS['TCA']['tx_ccc_data'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_ccc_data']['ctrl'],
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