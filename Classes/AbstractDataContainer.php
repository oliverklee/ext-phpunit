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
 * This class provides functions for reading and writing data, e.g., from settings or from a request.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_AbstractDataContainer {
	/**
	 * Returns the boolean value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return boolean the value for the given key, will be FALSE if there is no value for the given key
	 */
	public function getAsBoolean($key) {
		return (boolean) $this->get($key);
	}

	/**
	 * Returns the integer value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return integer the value for the given key, will be 0 if there is no value for the given key
	 */
	public function getAsInteger($key) {
		return intval($this->get($key));
	}

	/**
	 * Checks whether there is a non-zero integer for $key.
	 *
	 * @param string $key the key of the value to check, must not be empty
	 *
	 * @return boolean whether there is a non-zero integer for $key
	 */
	public function hasInteger($key) {
		return ($this->getAsInteger($key) !== 0);
	}

	/**
	 * Returns the string value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return string the value for the given key, will be "" if there is no value for the given key
	 */
	public function getAsString($key) {
		return strval($this->get($key));
	}

	/**
	 * Checks whether there is a non-empty string for $key.
	 *
	 * @param string $key the key of the value to check, must not be empty
	 *
	 * @return boolean whether there is a non-empty string for $key
	 */
	public function hasString($key) {
		return ($this->getAsString($key) !== '');
	}

	/**
	 * Returns the array value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return array the value for the given key, will be empty if there is no array value for the given key
	 */
	public function getAsArray($key) {
		$rawValue = $this->get($key);
		if (!is_array($rawValue)) {
			$rawValue = array();
		}

		return $rawValue;
	}

	/**
	 * Returns the value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return mixed the value for the given key, will be NULL if there is no value for the given key
	 */
	abstract protected function get($key);

	/**
	 * Checks that $key is non-empty.
	 *
	 * @param string $key the key to check, may be empty
	 *
	 * @throws InvalidArgumentException if $key is empty
	 *
	 * @return void
	 */
	protected function checkForNonEmptyKey($key) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1335021694);
		}
	}
}
?>