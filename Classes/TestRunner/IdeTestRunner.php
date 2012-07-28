<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Helmut Hummel <helmut.hummel@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * This class runs PHPUnit in CLI mode, and includes the
 * PHP boot script of an IDE.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Helmut Hummel <helmut.hummel@typo3.org>
 */
class Tx_Phpunit_TestRunner_IdeTestRunner extends Tx_Phpunit_TestRunner_AbstractCliTestRunner {
	/**
	 * Additional help text for the command line
	 *
	 * @var array
	 */
	protected $additionalHelp = array(
		'name' => 'Tx_Phpunit_TestRunner_IdeTestRunner',
		'synopsis' => 'phpunit_ide_testrunner <test or test folder> ###OPTIONS###',
		'description' => 'This script should only be run through an IDE',
		'examples' => '',
		'author' => '(c) 2012 Helmut Hummel <helmut.hummel@typo3.org>',
	);
}
	// Shift away myself
array_shift($_SERVER['argv']);

$ideBootScript = array_shift($_SERVER['argv']);
if (empty($ideBootScript) || !is_file($ideBootScript)) {
	throw new UnexpectedValueException('IDE Boot Script not found!', 1343498915);
}

/* @var $phpUnit Tx_Phpunit_TestRunner_IdeTestRunner */
$phpUnit = t3lib_div::makeInstance('Tx_Phpunit_TestRunner_IdeTestRunner');

require_once($ideBootScript);

?>