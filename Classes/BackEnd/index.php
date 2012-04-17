<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2012 Kasper Ligaard <kasperligaard@gmail.com>
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
 * Module "PHPUnit".
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (!defined('PATH_tslib')) {
	/**
	 * @var string
	 */
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

require_once('PHPUnit/Autoload.php');

$GLOBALS['LANG']->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xml');

/** @var $outputService Tx_PhpUnit_Service_OutputService */
$outputService = t3lib_div::makeInstance('Tx_PhpUnit_Service_OutputService');

/** @var $testListener Tx_PhpUnit_BackEnd_TestListener */
$testListener = t3lib_div::makeInstance('Tx_PhpUnit_BackEnd_TestListener');
$testListener->injectOutputService($outputService);

/** @var $module Tx_Phpunit_BackEnd_Module */
$module = t3lib_div::makeInstance('Tx_Phpunit_BackEnd_Module');
$module->injectOutputService($outputService);
$module->injectTestListener($testListener);
$module->main();
?>