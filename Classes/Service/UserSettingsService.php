<?php

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class provides functions for reading and writing the settings of the back-end user who is currently logged in.
 *
 * This class may only be used when a back-end user is logged in.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_UserSettingsService extends \Tx_Phpunit_AbstractDataContainer implements \Tx_Phpunit_Interface_UserSettingsService, SingletonInterface
{
    /**
     * @var string
     */
    const PHPUNIT_SETTINGS_KEY = 'Tx_Phpunit_BackEndSettings';

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
        if (!isset($this->getBackEndUser()->uc[self::PHPUNIT_SETTINGS_KEY][$key])) {
            return null;
        }

        return $this->getBackEndUser()->uc[self::PHPUNIT_SETTINGS_KEY][$key];
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

        $this->getBackEndUser()->uc[self::PHPUNIT_SETTINGS_KEY][$key] = $value;
        $this->getBackEndUser()->writeUC();
    }

    /**
     * Returns whether the given setting is active/allowed.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isActive($key)
    {
        switch ($key) {
            case 'thisSettingIsAlwaysInactive':
                $isActive = false;
                break;
            default:
                // If the given setting is not covered by any of the cases, it should be considered active.
                $isActive = true;
        }

        return $isActive;
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication
     */
    protected function getBackEndUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
