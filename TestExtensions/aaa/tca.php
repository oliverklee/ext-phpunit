<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tx_aaa_test'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_aaa_test']['ctrl'],
	'columns' => array(
		'hidden' => array(
			'config' => array(
				'type'	=> 'check',
			)
		),
	),
);