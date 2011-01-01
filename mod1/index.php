<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2011 Kasper Ligaard <kasperligaard@gmail.com>
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
 * Module "PHPUnit".
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

require_once('PHPUnit/Autoload.php');

$LANG->includeLLFile('EXT:phpunit/mod1/locallang.xml');

$SOBE = t3lib_div::makeInstance('tx_phpunit_module1');
$SOBE->main();
?>