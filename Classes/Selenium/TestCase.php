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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides helper functions that might be convenient when testing in
 * TYPO3. It extends \PHPUnit_Extensions_SeleniumTestCase, so you have access to
 * all of that class too.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_Selenium_TestCase extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * @var \Tx_Phpunit_Interface_ExtensionSettingsService
     */
    protected $extensionSettingsService = null;

    /**
     * @var \Tx_Phpunit_Interface_SeleniumService
     */
    protected $seleniumService = null;

    /**
     * The constructor.
     *
     * @param string $name
     * @param array $data
     * @param string $dataName
     * @param \Tx_Phpunit_Interface_ExtensionSettingsService|null $extensionSettingsService
     * @param \Tx_Phpunit_Interface_SeleniumService|null $seleniumService
     */
    public function __construct(
        $name = null,
        array $data = [],
        $dataName = '',
        \Tx_Phpunit_Interface_ExtensionSettingsService $extensionSettingsService = null,
        \Tx_Phpunit_Interface_SeleniumService $seleniumService = null
    ) {
        parent::__construct($name, $data, $dataName);

        if ($extensionSettingsService !== null) {
            $this->extensionSettingsService = $extensionSettingsService;
        } else {
            $this->extensionSettingsService = GeneralUtility::makeInstance(\Tx_Phpunit_Service_ExtensionSettingsService::class);
        }

        if ($seleniumService !== null) {
            $this->seleniumService = $seleniumService;
        } else {
            $this->seleniumService = GeneralUtility::makeInstance(
                'Tx_Phpunit_Service_SeleniumService',
                $this->extensionSettingsService
            );
        }

        $this->setBrowserUrl($this->getSeleniumBrowserUrl());
        $this->setPort($this->getSeleniumPort());
        $this->setBrowser($this->getSeleniumBrowser());
    }

    /**
     * Runs the test if the Selenium RC Server is reachable.
     *
     * If the server is not reachable, the tests will be marked as skipped, and
     * a message will be displayed giving a hint on which host/port the client
     * was looking for the Selenium server.
     *
     * @see \PHPUnit_Extensions_SeleniumTestCase::runTest()
     *
     * @return void
     */
    protected function runTest()
    {
        if ($this->isSeleniumServerRunning()) {
            parent::runTest();
        } else {
            $this->markTestSkipped(
                'Selenium RC server not reachable (host=' .
                $this->getSeleniumHost() . ', port=' .
                $this->getSeleniumPort() . ').'
            );
        }
    }

    /**
     * Tests if the Selenium RC server is running.
     *
     * @return bool TRUE if the server is reachable by opening a socket, FALSE otherwise
     */
    protected function isSeleniumServerRunning()
    {
        return $this->seleniumService->isSeleniumServerRunning();
    }

    /**
     * Returns the configured host name of the Selenium RC server.
     *
     * This function returns "localhost" if no host is configured.
     *
     * @return string host of the Selenium RC server, will not be empty
     */
    protected function getSeleniumHost()
    {
        return $this->seleniumService->getSeleniumHost();
    }

    /**
     * Returns the configured port number of the Selenium RC server.
     *
     * This functions returns 4444 (the standard Selenium RC port) if no port is
     * is configured
     *
     * @return int the Selenium RC server port, will be > 0
     */
    protected function getSeleniumPort()
    {
        return $this->seleniumService->getSeleniumPort();
    }

    /**
     * Returns the configured browser that should run the Selenium tests.
     *
     * This functions returns Firefox in chrome mode if no browser is configured.
     *
     * @return string Selenium RC browser, will not be empty
     */
    protected function getSeleniumBrowser()
    {
        return $this->seleniumService->getSeleniumBrowser();
    }

    /**
     * Returns the configured Selenium RC browser starting URL.
     *
     * This functions returns the TYPO3_SITE_URL if no URL is configured.
     *
     * @return string Selenium RC Browser URL, will not be empty
     */
    protected function getSeleniumBrowserUrl()
    {
        return $this->seleniumService->getSeleniumBrowserUrl();
    }
}
