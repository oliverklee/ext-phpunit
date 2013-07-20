<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012-2013 Helmut Hummel <helmut.hummel@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This runs PHPUnit in CLI mode, and includes the PHP boot script of an IDE.
 */

// Shift away myself
array_shift($_SERVER['argv']);

$ideBootScript = array_shift($_SERVER['argv']);
if (empty($ideBootScript) || !is_file($ideBootScript)) {
	throw new UnexpectedValueException('IDE Boot Script not found!', 1343498915);
}

/* @var $phpUnit Tx_Phpunit_TestRunner_IdeTestRunner */
$phpUnit = t3lib_div::makeInstance('Tx_Phpunit_TestRunner_IdeTestRunner');

require_once($ideBootScript);
?>