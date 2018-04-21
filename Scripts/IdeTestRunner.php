<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This runs PHPUnit in CLI mode, and includes the PHP boot script of an IDE.
 */

// Shift away myself
array_shift($_SERVER['argv']);

$ideBootScript = array_shift($_SERVER['argv']);
if (empty($ideBootScript) || !is_file($ideBootScript)) {
    throw new \UnexpectedValueException('IDE Boot Script not found!', 1343498915);
}

/* @var \Tx_Phpunit_TestRunner_IdeTestRunner $phpUnit */
$phpUnit = GeneralUtility::makeInstance(\Tx_Phpunit_TestRunner_IdeTestRunner::class);

require_once $ideBootScript;
