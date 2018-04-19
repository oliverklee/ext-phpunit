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
 * This runs PHPUnit in CLI mode, and includes the PHP boot script of an IDE.
 */

// Shift away myself
array_shift($_SERVER['argv']);

$ideBootScript = array_shift($_SERVER['argv']);
if (empty($ideBootScript) || !is_file($ideBootScript)) {
    throw new UnexpectedValueException('IDE Boot Script not found!', 1343498915);
}

/* @var Tx_Phpunit_TestRunner_IdeTestRunner $phpUnit */
$phpUnit = GeneralUtility::makeInstance(\Tx_Phpunit_TestRunner_IdeTestRunner::class);

require_once($ideBootScript);
