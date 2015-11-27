<?php
/*
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

defined('TYPO3_MODE') or die('Access denied.');

/** @var \TYPO3\CMS\Lang\LanguageService $languageService */
$languageService = $GLOBALS['LANG'];
$languageService->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xlf');

$namePrettifier = new PHPUnit_Util_TestDox_NamePrettifier();

/** @var Tx_Phpunit_Service_OutputService $outputService */
$outputService = GeneralUtility::makeInstance('Tx_Phpunit_Service_OutputService');

/** @var Tx_Phpunit_Service_UserSettingsService $userSettingsService */
$userSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_UserSettingsService');

/** @var Tx_Phpunit_BackEnd_TestListener $testListener */
$testListener = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_TestListener');
$testListener->injectNamePrettifier($namePrettifier);
$testListener->injectOutputService($outputService);

/** @var Tx_Phpunit_Service_ExtensionSettingsService $extensionSettingsService */
$extensionSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');

/** @var Tx_Phpunit_Service_UserSettingsService $userSettingsService */
$userSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_UserSettingsService');

/** @var Tx_Phpunit_Service_TestCaseService $testCaseService */
$testCaseService = GeneralUtility::makeInstance('Tx_Phpunit_Service_TestCaseService');
$testCaseService->injectUserSettingsService($userSettingsService);

/** @var Tx_Phpunit_Service_TestFinder $testFinder */
$testFinder = GeneralUtility::makeInstance('Tx_Phpunit_Service_TestFinder');
$testFinder->injectExtensionSettingsService($extensionSettingsService);

/** @var Tx_Phpunit_BackEnd_Request $request */
$request = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_Request');

/** @var PHPUnit_Util_TestDox_NamePrettifier */
$namePrettifier = GeneralUtility::makeInstance('PHPUnit_Util_TestDox_NamePrettifier');

/** @var Tx_Phpunit_BackEnd_Module $module */
$module = GeneralUtility::makeInstance('Tx_Phpunit_BackEnd_Module');
$module->injectRequest($request);
$module->injectNamePrettifier($namePrettifier);
$module->injectOutputService($outputService);
$module->injectUserSettingsService($userSettingsService);
$module->injectTestListener($testListener);
$module->injectTestFinder($testFinder);
$module->injectTestCaseService($testCaseService);
$module->main();