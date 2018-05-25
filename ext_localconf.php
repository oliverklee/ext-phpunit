<?php
defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE === 'BE'
    && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 8000000) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'PHPUnitAJAX::saveCheckbox',
        'Tx_Phpunit_BackEnd_Ajax->ajaxBroker'
    );
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'] = [
    'EXT:phpunit/Scripts/ManualCliTestRunner.php',
    '_CLI_phpunit',
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit_ide_testrunner'] = [
    'EXT:phpunit/Scripts/IdeTestRunner.php',
    '_CLI_phpunit',
];
