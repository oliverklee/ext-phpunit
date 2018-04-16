<?php
defined('TYPO3_MODE') or die('Access denied.');

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('phpunit');

if (!class_exists('PHPUnit_Framework_TestCase') || !class_exists('PHPUnit_Extensions_SeleniumTestCase')
    || !class_exists('org\\bovigo\\vfs\\vfsStream')) {
    require_once($extPath . 'Resources/Private/Libraries/phpunit-library.phar');
}

if (TYPO3_MODE === 'BE'
    && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 8000000) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'PHPUnitAJAX::saveCheckbox',
        'Tx_Phpunit_BackEnd_Ajax->ajaxBroker'
    );
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'] = [
    'EXT:' . $_EXTKEY . '/Scripts/ManualCliTestRunner.php',
    '_CLI_phpunit',
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit_ide_testrunner'] = [
    'EXT:' . $_EXTKEY . '/Scripts/IdeTestRunner.php',
    '_CLI_phpunit',
];
