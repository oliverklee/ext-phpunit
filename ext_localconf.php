<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'] = [
    'EXT:phpunit/Scripts/ManualCliTestRunner.php',
    '_CLI_phpunit',
];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit_ide_testrunner'] = [
    'EXT:phpunit/Scripts/IdeTestRunner.php',
    '_CLI_phpunit',
];
