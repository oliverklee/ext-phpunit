<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nicole Cordes <nicole.cordes@googlemail.com>
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
 * This interface provides functions for converting values into different types.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 * @author Oliver Klee <typo3-coding@oliverklee,de>
 */
interface Tx_Phpunit_Interface_ConvertService {
	/**
	 * Returns the boolean value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return boolean the value for the given key, will be FALSE if there is no value for the given key
	 */
	public function getAsBoolean($key);

	/**
	 * Returns the integer value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return integer the value for the given key, will be 0 if there is no value for the given key
	 */
	public function getAsInteger($key);

	/**
	 * Checks whether there is a non-zero integer for $key.
	 *
	 * @param string $key the key of the value to check, must not be empty
	 *
	 * @return boolean whether there is a non-zero integer for $key
	 */
	public function hasInteger($key);

	/**
	 * Returns the string value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return string the value for the given key, will be "" if there is no value for the given key
	 */
	public function getAsString($key);

	/**
	 * Checks whether there is a non-empty string for $key.
	 *
	 * @param string $key the key of the value to check, must not be empty
	 *
	 * @return boolean whether there is a non-empty string for $key
	 */
	public function hasString($key);

	/**
	 * Returns the array value stored for the key $key.
	 *
	 * @param string $key the key of the value to retrieve, must not be empty
	 *
	 * @return array the value for the given key, will be empty if there is no array value for the given key
	 */
	public function getAsArray($key);
}
?>