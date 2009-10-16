<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Robert Lemke (robert@typo3.org)
*  (c) 2008-2009 Kasper Ligaard (kli@systime.dk)
*  (c) 2008-2009 Soren Soltveit (sso@systime.dk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class provides helper functions, that might be convenient when testing in
 * Typo3. It extends PHPUnit_Framework_TestCase, so you have access to all of that
 * class too.
 *
 */
require_once('PHPUnit/Framework.php');

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

class tx_phpunit_testcase extends PHPUnit_Framework_TestCase {
	/**
	 * @var boolean
	 */
	protected $backupGlobals = false;

	/**
	 * @var boolean
	 */
	protected $backupStaticAttributes = false;

	/**
	 * Constructs a test case with the given name.
	 *
	 * @param  string $name
	 * @param  array  $data
	 * @param  string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
	}

	/**
	 * Roughly simulates the frontend although being in the backend.
	 *
	 * @return	void
	 * @todo	This is a quick hack, needs proper implementation
	 */
	protected function simulateFrontendEnviroment() {
		global $TSFE, $TYPO3_CONF_VARS;

			// FIXME: Currently bad workaround which only initializes a few things, not really what you'd call a frontend enviroment

		require_once(PATH_tslib.'class.tslib_fe.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		require_once(PATH_t3lib.'class.t3lib_userauth.php');
		require_once(PATH_tslib.'class.tslib_feuserauth.php');
		require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib.'class.t3lib_cs.php');

		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$TSFE = new $temp_TSFEclassName(
				$TYPO3_CONF_VARS,
				t3lib_div::_GP('id'),
				t3lib_div::_GP('type'),
				t3lib_div::_GP('no_cache'),
				t3lib_div::_GP('cHash'),
				t3lib_div::_GP('jumpurl'),
				t3lib_div::_GP('MP'),
				t3lib_div::_GP('RDCT')
			);
		$TSFE->connectToDB();
		$TSFE->initTemplate();
		$TSFE->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$TSFE->sys_page->init(true);
		$TSFE->config = array();		// Must be filled with actual config!
	}
}


/**
 * This class is provided for backwards compatibility with the extension t3unit
 * t3unit is based on PHPUnit version 2 (known as PHPUnit2)
 */
class tx_t3unit_testcase extends PHPUnit_Framework_TestCase {

}

?>