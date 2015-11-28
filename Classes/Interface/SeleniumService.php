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
 * This interface provides functions for using Selenium RC.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
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
