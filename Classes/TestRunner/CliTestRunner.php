<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * With this TestRunner, you can run PHPUnit manually from the command line.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Helmut Hummel <helmut.hummel@typo3.org>
 */
class Tx_Phpunit_TestRunner_CliTestRunner extends Tx_Phpunit_TestRunner_AbstractCliTestRunner {
	/**
	 * Runs PHPUnit.
	 *
	 * @return void
	 */
	public function run() {
		/** @var string */
		define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
		PHPUnit_TextUI_Command::main();
	}
}
