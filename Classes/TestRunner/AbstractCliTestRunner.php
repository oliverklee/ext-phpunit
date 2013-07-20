<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2013 AOE media GmbH <dev@aoemedia.de>
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

if (!defined('TYPO3_cliMode')) {
	die('Access denied: CLI only.');
}

/**
 * Abstract TestRunner class. Can be used to implement other TestRunners which need CLI scope.
 *
 * Currently only CliTestRunner and IdeTestRunner are implemented.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 */
abstract class Tx_Phpunit_TestRunner_AbstractCliTestRunner extends t3lib_cli {
	/**
	 * Additional help text for the command line
	 *
	 * @var array
	 */
	protected $additionalHelp = array();

	/**
	 * definition of the extension name
	 *
	 * @var string
	 */
	protected $extKey = 'phpunit_cli';

	/**
	 * The constructor.
	 */
	public function __construct() {
		setlocale(LC_NUMERIC, 'C');
		parent::__construct();

		$this->cli_help = array_merge(
			$this->cli_help,
			$this->additionalHelp
		);
	}
}
?>