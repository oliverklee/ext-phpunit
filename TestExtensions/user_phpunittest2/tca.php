<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['user_phpunittest2_test'] = array(
	'ctrl' => $GLOBALS['TCA']['user_phpunittest2_test']['ctrl'],
	'columns' => array(
		'hidden' => array(
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'config' => array(
				'type' => 'input',
				'eval' => 'date',
			),
		),
		'endtime' => array(
			'config' => array(
				'type' => 'input',
				'eval' => 'date',
			)
		),
		'title' => array(
			'config' => array(
				'type' => 'input',
			),
		),
	),
);