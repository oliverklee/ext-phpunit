<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2009 Kasper Ligaard (kasperligaard@gmail.com)
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
 * Module 'PHPUnit' for the 'phpunit' extension.
 *
 * @author	Kasper Ligaard <ligaard@daimi.au.dk>
 */

$LANG->includeLLFile('EXT:phpunit/mod1/locallang.xml');

require_once(t3lib_extMgm::extPath('phpunit') . 'class.tx_phpunit_testlistener.php');
require_once(t3lib_extMgm::extPath('phpunit') . 'class.tx_phpunit_testcase.php');
require_once(t3lib_extMgm::extPath('phpunit') . 'class.tx_phpunit_database_testcase.php');
// included for PHPUnit version string
require_once('PHPUnit/Runner/Version.php');
// included for PHPUnit version string
require_once('PHPUnit/Util/Report.php');
require_once('class.tx_phpunit_module1.php');

if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

// Which instance interface to create?
if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['phpunit']['mod1/class.tx_phpunit_module1.php']['main'])) {
    $classRef = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['phpunit']['mod1/class.tx_phpunit_module1.php']['main'];
    // Class must implement main(). What was previously init() should now happen in __construct().
	$SOBE = t3lib_div::getUserObj($classRef);
} else {
	$SOBE = new tx_phpunit_module1();
}

// runs our Script Object Back-End (SOBE)
$SOBE->main();

?>