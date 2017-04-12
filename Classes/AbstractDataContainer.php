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
 * This class provides functions for reading and writing data, e.g., from settings or from a request.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_AbstractDataContainer
{
    /**
     * Returns the boolean value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return bool the value for the given key, will be FALSE if there is no value for the given key
     */
    public function getAsBoolean($key)
    {
        return (bool)$this->get($key);
    }

    /**
     * Returns the integer value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return int the value for the given key, will be 0 if there is no value for the given key
     */
    public function getAsInteger($key)
    {
        return (int)$this->get($key);
    }

    /**
     * Checks whether there is a non-zero integer for $key.
     *
     * @param string $key the key of the value to check, must not be empty
     *
     * @return bool whether there is a non-zero integer for $key
     */
    public function hasInteger($key)
    {
        return $this->getAsInteger($key) !== 0;
    }

    /**
     * Returns the string value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return string the value for the given key, will be "" if there is no value for the given key
     */
    public function getAsString($key)
    {
        return (string)$this->get($key);
    }

    /**
     * Checks whether there is a non-empty string for $key.
     *
     * @param string $key the key of the value to check, must not be empty
     *
     * @return bool whether there is a non-empty string for $key
     */
    public function hasString($key)
    {
        return $this->getAsString($key) !== '';
    }

    /**
     * Returns the array value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return array the value for the given key, will be empty if there is no array value for the given key
     */
    public function getAsArray($key)
    {
        $rawValue = $this->get($key);
        if (!is_array($rawValue)) {
            $rawValue = [];
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
    protected function checkForNonEmptyKey($key)
    {
        if ($key === '') {
            throw new InvalidArgumentException('$key must not be empty.', 1335021694);
        }
    }
}
