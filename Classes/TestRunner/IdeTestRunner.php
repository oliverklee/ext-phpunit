<?php
/**
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
 * This class runs PHPUnit in CLI mode and includes the PHP boot script of an IDE.
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
	 * @var string[]
	 */
	protected $additionalHelp = array(
		'name' => 'Tx_Phpunit_TestRunner_IdeTestRunner',
		'synopsis' => 'phpunit_ide_testrunner <test or test folder> ###OPTIONS###',
		'description' => 'This script should only be run through an IDE.',
		'examples' => '',
		'author' => '(c) 2012-2013 Helmut Hummel <helmut.hummel@typo3.org>',
	);
}