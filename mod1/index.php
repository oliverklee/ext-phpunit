<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2007 Kasper Ligaard (ligaard@daimi.au.dk)
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

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   85: class tx_phpunitt3_module1 extends t3lib_SCbase
 *   93:     public function menuConfig()
 *  112:     public function main()
 *
 *              SECTION: Screen render functions
 *  179:     protected function runTests_render()
 *  200:     protected function runTests_renderIntro()
 *  223:     protected function runTests_renderIntro_renderExtensionSelector($extensionsWithTestSuites)
 *  258:     protected function runTests_renderIntro_renderTestSelector($extensionsWithTestSuites, $extensionKey)
 *  303:     protected function runTests_renderRunningTest()
 *  387:     protected function runTests_renderInfoAndProgressbar()
 *  403:     protected function about_render()
 *
 *              SECTION: Helper functions
 *  437:     protected function openNewWindowLink()
 *  456:     protected function getExtensionsWithTestSuites()
 *  466:     protected function traversePathForTestCases($path)
 *  502:     protected function simulateFrontendEnviroment()
 *
 * TOTAL FUNCTIONS: 13
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

# next four lines commented out because we now dispatch to mod.php
#unset($MCONF);
#require ('conf.php');
#require ($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:phpunit/mod1/locallang.xml');

require_once (PATH_t3lib.'class.t3lib_scbase.php');
/* FIXME: This should be made configurable, i.e. easily choose among the phpunit
*         version, that comes with this extension, or using PEAR installed phpunit.  
*/ 
// require_once (t3lib_extMgm::extPath('phpunit').'phpunit-3.0.5/Framework/TestSuite.php');
//require_once (t3lib_extMgm::extPath('phpunit').'phpunit-3.1.9/Framework/TestSuite.php');

require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_testlistener.php');
require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_testcase.php');
require_once (t3lib_extMgm::extPath('phpunit').'class.tx_phpunit_database_testcase.php');
require_once ('PHPUnit/Runner/Version.php'); // Included for PHPUnit versionstring.
require_once ('PHPUnit/Util/Report.php'); // Included for PHPUnit versionstring.
require_once ('class.tx_phpunit_module1.php');
require_once ('class.tx_phpunit_module1_mikkelricky.php');
require_once ('class.tx_phpunit_module1_ajax.php');

define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');

// Which instance interface to create?
if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['experimentalTestSuiteUI']) {
	$SOBE = new tx_phpunit_module1_mikkelricky();
} else if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['experimentalAjaxUI']) {
	$SOBE = new tx_phpunit_module1_ajax();
} else {
	$SOBE = new tx_phpunit_module1();
}
$SOBE->init();
$SOBE->main();

?>