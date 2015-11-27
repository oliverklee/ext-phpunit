<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "phpunit".
 *
 * Auto generated 27-11-2014 01:28
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'PHPUnit',
	'description' => 'Unit testing for TYPO3. Includes PHPUnit 4.4, Selenium, a BE test runner module, a CLI test runner, PhpStorm integration and a testing framework.',
	'category' => 'module',
	'shy' => 0,
	'version' => '4.4.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'Classes/BackEnd',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => '',
	'doNotLoadInFE' => 1,
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.4.0-5.6.99',
			'typo3' => '6.2.4-7.4.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'autoload' => array(
		'classmap' => array(
			'Classes',
		),
	),
	'_md5_values_when_last_written' => '',
);
