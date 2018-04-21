<?php

/**
 * This interface provides functions for reading and writing the settings of the back-end user who is currently logged in.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_UserSettingsService extends \Tx_Phpunit_Interface_ConvertService
{
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
