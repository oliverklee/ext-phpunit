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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides helper functions that might be convenient when testing in
 * TYPO3. It extends PHPUnit_Extensions_SeleniumTestCase, so you have access to
 * all of that class too.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_Selenium_TestCase extends PHPUnit_Extensions_Selenium2TestCase {
	/**
	 * the default Selenium server host address
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_HOST = 'localhost';

	/**
	 * the default Selenium server port
	 *
	 * @var int
	 */
	const DEFAULT_SELENIUM_PORT = 4444;

	/**
	 * the default Selenium browser
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_BROWSER = '*chrome';

	/**
	 * the default Selenium browser URL
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_BROWSER_URL = '/';

	/**
	 * @var Tx_Phpunit_Interface_ExtensionSettingsService
	 */
	protected $extensionSettingsService = NULL;

	/**
	 * The constructor.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param string $dataName
	 * @param Tx_Phpunit_Interface_ExtensionSettingsService $extensionSettingsService
	 *        the extension settings service to use
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '', Tx_Phpunit_Interface_ExtensionSettingsService $extensionSettingsService = NULL) {
		if ($extensionSettingsService === NULL) {
			$extensionSettingsService = GeneralUtility::makeInstance('Tx_Phpunit_Service_ExtensionSettingsService');
		}
		$this->extensionSettingsService = $extensionSettingsService;

		$browser = array(
			'browser' => $this->getSeleniumBrowser(),
			'host' => $this->getSeleniumHost(),
			'port' => $this->getSeleniumPort(),
		);
		parent::__construct($name, $data, $dataName, $browser);

		$this->setBrowserUrl($this->getSeleniumBrowserUrl());
	}

	/**
	 * Runs the test if the Selenium RC Server is reachable.
	 *
	 * If the server is not reachable, the tests will be marked as skipped, and
	 * a message will be displayed giving a hint on which host/port the client
	 * was looking for the Selenium server.
	 *
	 * @see PHPUnit_Extensions_SeleniumTestCase::runTest()
	 *
	 * @return void
	 */
	protected function runTest() {
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
	protected function isSeleniumServerRunning() {
		$seleniumServerIsRunning = FALSE;

		$errorLevel = 0;
		$errorMessage = '';
		$timeout = 1;
		$socket = @fsockopen(
			$this->getSeleniumHost(), $this->getSeleniumPort(),
			$errorLevel, $errorMessage, $timeout
		);

		if ($socket !== FALSE) {
			$seleniumServerIsRunning = TRUE;
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
	protected function getSeleniumHost() {
		return $this->extensionSettingsService->hasString('selenium_host')
			? $this->extensionSettingsService->getAsString('selenium_host') : self::DEFAULT_SELENIUM_HOST;
	}

	/**
	 * Returns the configured port number of the Selenium RC server.
	 *
	 * This functions returns 4444 (the standard Selenium RC port) if no port is
	 * is configured
	 *
	 * @return int the Selenium RC server port, will be > 0
	 */
	protected function getSeleniumPort() {
		return $this->extensionSettingsService->hasInteger('selenium_port')
			? $this->extensionSettingsService->getAsInteger('selenium_port') : self::DEFAULT_SELENIUM_PORT;
	}

	/**
	 * Returns the configured browser that should run the Selenium tests.
	 *
	 * This functions returns Firefox in chrome mode if no browser is configured.
	 *
	 * @return string Selenium RC browser, will not be empty
	 */
	protected function getSeleniumBrowser() {
		return $this->extensionSettingsService->hasString('selenium_browser')
			? $this->extensionSettingsService->getAsString('selenium_browser') : self::DEFAULT_SELENIUM_BROWSER;
	}

	/**
	 * Returns the configured Selenium RC browser starting URL.
	 *
	 * This functions returns the TYPO3_SITE_URL if no URL is configured.
	 *
	 * @return string Selenium RC Browser URL, will not be empty
	 */
	protected function getSeleniumBrowserUrl() {
		return $this->extensionSettingsService->hasString('selenium_browserurl')
			? $this->extensionSettingsService->getAsString('selenium_browserurl')
			: rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), self::DEFAULT_SELENIUM_BROWSER_URL);
	}
}