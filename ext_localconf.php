<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'] = [
    'EXT:phpunit/Scripts/ManualCliTestRunner.php',
    '_CLI_phpunit',
];
