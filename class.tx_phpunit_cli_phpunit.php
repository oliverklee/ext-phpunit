<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 AOE media GmbH <dev@aoemedia.de>
*  All rights reserved
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

if (!defined ('TYPO3_cliMode'))
	die ('Access denied: CLI only.');

require_once PATH_t3lib . 'class.t3lib_cli.php';
require_once t3lib_extMgm::extPath('phpunit') . 'class.tx_phpunit_testcase.php';
require_once t3lib_extMgm::extPath('phpunit') . 'class.tx_phpunit_database_testcase.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * class to run phpuniit
 */
class tx_phpunit_cli_phpunit extends t3lib_cli {

	/**
	 * @var string
	 */
	protected $prefixId = 'tx_phpunit_cli_phpunit'; // Same as class name

	/**
	 * Path to this script relative to the extension dir
	 * @var string
	 */
	protected $scriptRelPath = 'class.tx_phpunit_cli_phpunit.php';

	/**
	 * definition of the extension name
	 * @var string
	 */
	protected $extKey = 'phpunit_cli';

	/**
	 * @var tx_eft_system_logger_backend_FileBackend
	 */
	protected $fileLogger;

	/**
	 * constructor
	 */
	public function __construct() {
		parent::t3lib_cli();
		$this->cli_options = array_merge($this->cli_options, array());
		$this->cli_help = array_merge($this->cli_help, array(
			'name'        => 'tx_phpunit_cli_phpunit',
			'synopsis'    => $this->extKey . ' command [clientId] ###OPTIONS###',
			'description' => 'This script can update a list of several caches (per CLI-call can one cache be updated)',
			'examples'    => 'typo3/cli_dispatch.phpsh',
			'author'      => '(c) 2009 AOE media GmbH <dev@aoemedia.de>',
		));
	}

	/**
	 * main function which detects the action and call the related methods
	 *
	 * @param array $argv array contains the arguments, which were post via CLI
	 */
	public function cli_main() {
		$this->cli_validateArgs();

		require_once 'PHPUnit/Util/Filter.php';
		PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
		require 'PHPUnit/TextUI/Command.php';
		define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
		PHPUnit_TextUI_Command::main();
	}
}

$phpunit = t3lib_div::makeInstance('tx_phpunit_cli_phpunit'); /* @var $phpunit tx_phpunit_cli_phpunit */
$phpunit->cli_main();
?>