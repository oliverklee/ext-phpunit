<?php

/**
 * This class provides functions for reading and writing testing data, e.g., fake settings or a fake request.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_TestingDataContainer extends \Tx_Phpunit_AbstractDataContainer implements
    \Tx_Phpunit_Interface_UserSettingsService,
    \Tx_Phpunit_Interface_ExtensionSettingsService,
    \Tx_Phpunit_Interface_Request
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Returns the value stored for the key $key.
     *
     * @param string $key the key of the value to retrieve, must not be empty
     *
     * @return mixed the value for the given key, will be NULL if there is no value for the given key
     */
    protected function get($key)
    {
        $this->checkForNonEmptyKey($key);
        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * Sets the value for the key $key.
     *
     * @param string $key the key of the value to set, must not be empty
     * @param mixed $value the value to set
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->checkForNonEmptyKey($key);

        $this->data[$key] = $value;
    }
}
