<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This interface provides functions for reading data from a POST/GET request.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_Request extends Tx_Phpunit_Interface_ConvertService {
	/**
	 * @var string
	 */
	const PARAMETER_NAMESPACE = 'tx_phpunit';

	/**
	 * @var string
	 */
	const PARAMETER_KEY_COMMAND = 'command';

	/**
	 * @var string
	 */
	const PARAMETER_KEY_TESTABLE = 'extSel';

	/**
	 * @var string
	 */
	const PARAMETER_KEY_TESTCASE = 'testCaseFile';

	/**
	 * @var string
	 */
	const PARAMETER_KEY_TEST = 'testname';

	/**
	 * @var string
	 */
	const PARAMETER_KEY_EXECUTE = 'bingo';
}
?>