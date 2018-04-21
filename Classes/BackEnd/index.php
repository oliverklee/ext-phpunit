<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Module "PHPUnit".
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
defined('TYPO3_MODE') or die('Access denied.');

/** @var \TYPO3\CMS\Lang\LanguageService $languageService */
$languageService = $GLOBALS['LANG'];
$languageService->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xlf');

$namePrettifier = new \PHPUnit_Util_TestDox_NamePrettifier();

/** @var \Tx_Phpunit_Service_OutputService $outputService */
$outputService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_OutputService::class);

/** @var \Tx_Phpunit_Service_UserSettingsService $userSettingsService */
$userSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_UserSettingsService::class);

/** @var \Tx_Phpunit_BackEnd_TestListener $testListener */
$testListener = GeneralUtility::makeInstance(\Tx_Phpunit_BackEnd_TestListener::class);
$testListener->injectNamePrettifier($namePrettifier);
$testListener->injectOutputService($outputService);

/** @var \Tx_Phpunit_Service_ExtensionSettingsService $extensionSettingsService */
$extensionSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_ExtensionSettingsService::class);

/** @var \Tx_Phpunit_Service_UserSettingsService $userSettingsService */
$userSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_UserSettingsService::class);

/** @var \Tx_Phpunit_Service_TestCaseService $testCaseService */
$testCaseService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_TestCaseService::class);
$testCaseService->injectUserSettingsService($userSettingsService);

/** @var \Tx_Phpunit_Service_TestFinder $testFinder */
$testFinder = GeneralUtility::makeInstance(\Tx_Phpunit_Service_TestFinder::class);
$testFinder->injectExtensionSettingsService($extensionSettingsService);

/** @var \Tx_Phpunit_BackEnd_Request $request */
$request = GeneralUtility::makeInstance(\Tx_Phpunit_BackEnd_Request::class);

/** @var \PHPUnit_Util_TestDox_NamePrettifier */
$namePrettifier = GeneralUtility::makeInstance(\PHPUnit_Util_TestDox_NamePrettifier::class);

/** @var \Tx_Phpunit_BackEnd_Module $module */
$module = GeneralUtility::makeInstance(\Tx_Phpunit_BackEnd_Module::class);
$module->injectRequest($request);
$module->injectNamePrettifier($namePrettifier);
$module->injectOutputService($outputService);
$module->injectUserSettingsService($userSettingsService);
$module->injectTestListener($testListener);
$module->injectTestFinder($testFinder);
$module->injectTestCaseService($testCaseService);
$module->main();
