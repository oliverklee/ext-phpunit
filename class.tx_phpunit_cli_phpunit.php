<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2010 AOE media GmbH <dev@aoemedia.de>
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

require_once('PHPUnit/Autoload.php');

/**
 * Class tx_phpunit_cli_phpunit for the "phpunit" extension.
 *
 * This class runs PHPUnit in CLI mode.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 */
class tx_phpunit_cli_phpunit extends t3lib_cli {
	/**
	 * same as class name
	 *
	 * @var string
	 */
	protected $prefixId = 'tx_phpunit_cli_phpunit';

	/**
	 * path to this script relative to the extension dir
	 *
	 * @var string
	 */
	protected $scriptRelPath = 'class.tx_phpunit_cli_phpunit.php';

	/**
	 * definition of the extension name
	 *
	 * @var string
	 */
	protected $extKey = 'phpunit_cli';

	/**
	 * @var tx_eft_system_logger_backend_FileBackend
	 */
	protected $fileLogger;

	/**
	 * The constructor.
	 */
	public function __construct() {
		setlocale(LC_NUMERIC, 'C');

		parent::t3lib_cli();
		$this->cli_options = array_merge($this->cli_options, array());
		$this->cli_help = array_merge($this->cli_help, array(
			'name' => 'tx_phpunit_cli_phpunit',
			'synopsis' => $this->extKey . ' command [clientId] ###OPTIONS###',
			'description' => 'This script can update a list of several caches (per CLI-call can one cache be updated)',
			'examples' => 'typo3/cli_dispatch.phpsh',
			'author' => '(c) 2009 AOE media GmbH <dev@aoemedia.de>',
		));
	}

	/**
	 * Detects the action and calls the related methods.
	 *
	 * @param array $argv array contains the arguments, which were post via CLI
	 */
	public function cli_main() {
		define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
		PHPUnit_TextUI_Command::main();
	}
}

$phpunit = t3lib_div::makeInstance('tx_phpunit_cli_phpunit'); /* @var $phpunit tx_phpunit_cli_phpunit */
$phpunit->cli_main();
?>