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
 * This class provides functions for reading data from a POST/GET request.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_Request extends Tx_Phpunit_AbstractDataContainer
	implements Tx_Phpunit_Interface_Request, t3lib_Singleton
{
	/**
	 * @var array
	 */
	private $cachedRequestData = array();

	/**
	 * @var boolean
	 */
	private $requestDataHasBeenRetrieved = FALSE;

	/**
	 * Returns the value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return mixed the value for the given key, will be NULL if there is no value for the given key
	 */
	protected function get($key) {
		$this->checkForNonEmptyKey($key);
		if (!$this->requestDataHasBeenRetrieved) {
			$this->retrieveRequestData();
		}
		if (!isset($this->cachedRequestData[$key])) {
			return NULL;
		}

		return $this->cachedRequestData[$key];
	}

	/**
	 * Retrieves the EM configuration for the PHPUnit extension.
	 *
	 * @return void
	 */
	protected function retrieveRequestData() {
		$this->cachedRequestData = t3lib_div::_GP(Tx_Phpunit_Interface_Request::PARAMETER_NAMESPACE);

		$this->requestDataHasBeenRetrieved = TRUE;
	}
}
?>