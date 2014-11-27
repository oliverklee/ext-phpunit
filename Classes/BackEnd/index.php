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

use TYPO3\CMS\Core\Utility\GeneralUtility;

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

$GLOBALS['LANG']->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xml');

$namePrettifier = new PHPUnit_Util_TestDox_NamePrettifier();

/** @var $outputService Tx_Phpunit_Service_OutputService */
$outputService = GeneralUtility::makeInstance('Tx_Phpunit_Service_OutputService');

/** @var $userSettingsService Tx_Phpunit_Service_UserSettingsService */
$userSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_UserSettingsService');

/** @var $testListener Tx_Phpunit_BackEnd_TestListener */
$testListener = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_TestListener');
$testListener->injectNamePrettifier($namePrettifier);
$testListener->injectOutputService($outputService);

/** @var $extensionSettingsService Tx_Phpunit_Service_ExtensionSettingsService */
$extensionSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');

/** @var $userSettingsService Tx_Phpunit_Service_UserSettingsService */
$userSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_UserSettingsService');

/** @var $testCaseService Tx_Phpunit_Service_TestCaseService */
$testCaseService = GeneralUtility::makeInstance('Tx_Phpunit_Service_TestCaseService');
$testCaseService->injectUserSettingsService($userSettingsService);

/** @var $testFinder Tx_Phpunit_Service_TestFinder */
$testFinder = GeneralUtility::makeInstance('Tx_Phpunit_Service_TestFinder');
$testFinder->injectExtensionSettingsService($extensionSettingsService);

/** @var $request Tx_Phpunit_BackEnd_Request */
$request = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_Request');

/** @var $module Tx_Phpunit_BackEnd_Module */
$module = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_Module');
$module->injectRequest($request);
$module->injectOutputService($outputService);
$module->injectUserSettingsService($userSettingsService);
$module->injectTestListener($testListener);
$module->injectTestFinder($testFinder);
$module->injectTestCaseService($testCaseService);
$module->main();