<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012-2013 Helmut Hummel <helmut.hummel@typo3.org>
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
		require_once('PHPUnit/Autoload.php');
		/** @var string */
		define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
		PHPUnit_TextUI_Command::main();
	}
}
?>