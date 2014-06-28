<?php
/**
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

/**
 * This interface provides functions for reading and writing the settings of the back-end user who is currently logged in.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_UserSettingsService extends Tx_Phpunit_Interface_ConvertService {
	/**
	 * Sets the value for the key $key.
	 *
	 * @param string $key the key of the value to set, must not be empty
	 * @param mixed $value the value to set
	 *
	 * @return void
	 */
	public function set($key, $value);
}