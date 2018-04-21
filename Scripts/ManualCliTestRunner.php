<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/* @var \Tx_Phpunit_TestRunner_CliTestRunner $phpUnit */
$phpUnit = GeneralUtility::makeInstance(\Tx_Phpunit_TestRunner_CliTestRunner::class);
$phpUnit->run();
