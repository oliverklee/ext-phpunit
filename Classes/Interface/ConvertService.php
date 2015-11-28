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

/**
 * This interface provides functions for converting values into different types.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 * @author Oliver Klee <typo3-coding@oliverklee,de>
 */
interface Tx_Phpunit_Interface_ConvertService
{
    /**
     * Returns the boolean value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return bool the value for the given key, will be FALSE if there is no value for the given key
     */
    public function getAsBoolean($key);

    /**
     * Returns the integer value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return int the value for the given key, will be 0 if there is no value for the given key
     */
    public function getAsInteger($key);

    /**
     * Checks whether there is a non-zero integer for $key.
     *
     * @param string $key the key of the value to check, must not be empty
     *
     * @return bool whether there is a non-zero integer for $key
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
     * @return bool whether there is a non-empty string for $key
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