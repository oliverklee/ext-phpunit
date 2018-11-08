<?php

/**
 * This interface provides functions for using Selenium RC.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
interface Tx_Phpunit_Interface_SeleniumService
{
    /**
     * Tests if the Selenium RC server is running.
     *
     * @return bool TRUE if the server is running, FALSE otherwise
     */
    public function isSeleniumServerRunning();

    /**
     * Returns the configured host name of the Selenium RC server.
     *
     * @return string host of the Selenium RC server, will not be empty
     */
    public function getSeleniumHost();

    /**
     * Returns the configured port number of the Selenium RC server.
     *
     * @return int the Selenium RC server port, will be > 0
     */
    public function getSeleniumPort();

    /**
     * Returns the configured browser that should run the Selenium tests.
     *
     * @return string Selenium RC browser, will not be empty
     */
    public function getSeleniumBrowser();

    /**
     * Returns the configured Selenium RC browser starting URL.
     *
     * @return string Selenium RC Browser URL, will not be empty
     */
    public function getSeleniumBrowserUrl();
}
