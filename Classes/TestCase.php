<?php
 /***************************************************************
 * Copyright notice
 *
 * (c) 2005-2012 Robert Lemke (robert@typo3.org)
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
 * This class provides helper functions that might be convenient when testing in
 * TYPO3. It extends PHPUnit_Framework_TestCase, so you have access to all of
 * that class as well.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Robert Lemke <robert@typo3.org>
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Soren Soltveit <sso@systime.dk>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_TestCase extends PHPUnit_Framework_TestCase {
	/**
	 * whether global variables should be backuped
	 *
	 * @var boolean
	 */
	protected $backupGlobals = FALSE;

	/**
	 * whether static attributes should be backuped
	 *
	 * @var boolean
	 */
	protected $backupStaticAttributes = FALSE;
}
?>