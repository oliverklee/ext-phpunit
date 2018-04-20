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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions for using Selenium RC.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class Tx_Phpunit_Service_SeleniumService implements \Tx_Phpunit_Interface_SeleniumService, SingletonInterface
{
    /**
     * @var \Tx_Phpunit_Interface_ExtensionSettingsService
     */
    protected $extensionSettingsService = null;

    /**
     * The constructor.
     *
     * @param \Tx_Phpunit_Interface_ExtensionSettingsService|null $extensionSettingsService
     */
    public function __construct(
        \Tx_Phpunit_Interface_ExtensionSettingsService $extensionSettingsService = null
    ) {
        if ($extensionSettingsService !== null) {
            $this->extensionSettingsService = $extensionSettingsService;
        } else {
            $this->extensionSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_ExtensionSettingsService::class);
        }
    }

    /**
     * Tests if the Selenium RC server is running.
     *
     * @return bool TRUE if the server is reachable by opening a socket, FALSE otherwise
     */
    public function isSeleniumServerRunning()
    {
        $seleniumServerIsRunning = false;

        $errorLevel = 0;
        $errorMessage = '';
        $timeout = 1;
        $socket = @fsockopen(
            $this->getSeleniumHost(),
            $this->getSeleniumPort(),
            $errorLevel,
            $errorMessage,
            $timeout
        );

        if ($socket !== false) {
            $seleniumServerIsRunning = true;
            fclose($socket);
        }

        return $seleniumServerIsRunning;
    }

    /**
     * Returns the configured host name of the Selenium RC server.
     *
     * This function returns "localhost" if no host is configured.
     *
     * @return string host of the Selenium RC server, will not be empty
     */
    public function getSeleniumHost()
    {
        return $this->extensionSettingsService->getAsString('selenium_host');
    }

    /**
     * Returns the configured port number of the Selenium RC server.
     *
     * This functions returns 4444 (the standard Selenium RC port) if no port is
     * is configured
     *
     * @return int the Selenium RC server port, will be > 0
     */
    public function getSeleniumPort()
    {
        return $this->extensionSettingsService->getAsInteger('selenium_port');
    }

    /**
     * Returns the configured browser that should run the Selenium tests.
     *
     * This functions returns Firefox in chrome mode if no browser is configured.
     *
     * @return string Selenium RC browser, will not be empty
     */
    public function getSeleniumBrowser()
    {
        return $this->extensionSettingsService->getAsString('selenium_browser');
    }

    /**
     * Returns the configured Selenium RC browser starting URL.
     *
     * This functions returns the TYPO3_SITE_URL if no URL is configured.
     *
     * @return string Selenium RC Browser URL, will not be empty
     */
    public function getSeleniumBrowserUrl()
    {
        return $this->extensionSettingsService->hasString('selenium_browserurl')
            ? $this->extensionSettingsService->getAsString('selenium_browserurl')
            : rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), '/');
    }
}
