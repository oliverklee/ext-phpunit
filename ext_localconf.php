<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once(t3lib_extMgm::extPath('phpunit') . 'Composer/vendor/autoload.php');
require_once(t3lib_extMgm::extPath('phpunit') . 'Migrations/vfsStream.php');

/** @var $extensionSettingsService Tx_Phpunit_Service_ExtensionSettingsService */
$extensionSettingsService = t3lib_div::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');
$composerPhpUnitPath = t3lib_extMgm::extPath('phpunit') . 'Composer/vendor/phpunit/phpunit/';
if ($extensionSettingsService->hasString('phpunitlib')) {
	$userPhpUnitPath = rtrim(t3lib_div::fixWindowsFilePath($extensionSettingsService->getAsString('phpunitlib')), '/');
	if (is_dir($userPhpUnitPath . '/PHPUnit')) {
		$composerPhpUnitPath = $userPhpUnitPath . '/';
	}
}
unset($extensionSettingsService);

define(TX_PHPUNITLIB_EXTPATH, $composerPhpUnitPath);
set_include_path(TX_PHPUNITLIB_EXTPATH . PATH_SEPARATOR . get_include_path());
unset($composerPhpUnitPath);

$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['Tx_Phpunit_BackEnd_Ajax']
	= 'typo3conf/ext/phpunit/Classes/BackEnd/Ajax.php:Tx_Phpunit_BackEnd_Ajax->ajaxBroker';

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
?>