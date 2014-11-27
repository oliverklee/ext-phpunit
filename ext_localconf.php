<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$composerAutoloadFile = ExtensionManagementUtility::extPath('phpunit') . 'Composer/vendor/autoload.php';

/** @var $extensionSettingsService Tx_Phpunit_Service_ExtensionSettingsService */
$extensionSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');
$composerPhpUnitPath = ExtensionManagementUtility::extPath('phpunit') . 'Composer/vendor/phpunit/phpunit/';
if ($extensionSettingsService->hasString('composerpath')) {
	$userComposerPath = rtrim(GeneralUtility::fixWindowsFilePath($extensionSettingsService->getAsString('composerpath')), '/');
	if (is_dir($userComposerPath . '/vendor/') && is_file($userComposerPath . '/vendor/autoload.php')) {
		if (set_include_path($userComposerPath . PATH_SEPARATOR . get_include_path()) !== FALSE) {
			$composerAutoloadFile = $userComposerPath . '/vendor/autoload.php';
		}
	}
}
unset($extensionSettingsService);

require_once($composerAutoloadFile);
require_once(ExtensionManagementUtility::extPath('phpunit') . 'Migrations/vfsStream.php');

$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Tx_Phpunit_BackEnd_Ajax'] =
	'typo3conf/ext/phpunit/Classes/BackEnd/Ajax.php:Tx_Phpunit_BackEnd_Ajax->ajaxBroker';

if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'] = array(
		'EXT:' . $_EXTKEY . '/Scripts/ManualCliTestRunner.php',
		'_CLI_phpunit',
	);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit_ide_testrunner'] = array(
		'EXT:' . $_EXTKEY . '/Scripts/IdeTestRunner.php',
		'_CLI_phpunit',
	);
}