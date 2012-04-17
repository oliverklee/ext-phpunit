<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['user_phpunittest_test'] = array(
	'ctrl' => $TCA['user_phpunittest_test']['ctrl'],
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
			),
		),
		'title' => array(
			'config' => array(
				'type' => 'input',
			),
		),
	),
);
?>