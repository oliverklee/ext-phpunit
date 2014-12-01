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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Carsten Koenig <ck@carsten-koenig.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Selenium_TestCaseTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Selenium_TestCase|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $extensionSettingsService = NULL;

	protected function setUp() {
		if (!class_exists('PHPUnit_Extensions_Selenium2TestCase', TRUE)) {
			$this->markTestSkipped('PHPUnit Selenium is not installed.');
		}

		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject = $this->getMock(
			$this->createAccessibleProxyClass(),
			array('isSeleniumServerRunning'),
			array(NULL, array(), '', $this->extensionSettingsService)
		);
		$this->subject->expects($this->any())->method('isSeleniumServerRunning')->will($this->returnValue(TRUE));
	}

	/*
	 * Utility functions
	 */

	/**
	 * This function creates a subclass of Tx_Phpunit_Selenium_TestCase with
	 * some attributes and methods made public.
	 *
	 * @return string class name, will not be empty
	 */
	private function createAccessibleProxyClass() {
		$className = uniqid('Tx_Phpunit_Selenium_AccessibleTestCase');

		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_Selenium_TestCase {' .
				'  ' .
				'  public function getSeleniumBrowser() {' .
				'    return parent::getSeleniumBrowser();' .
				'  }' .
				'  public function getSeleniumBrowserUrl() {' .
				'    return parent::getSeleniumBrowserUrl();' .
				'  }' .
				'  public function getSeleniumHost() {' .
				'    return parent::getSeleniumHost();' .
				'  }' .
				'  public function getSeleniumPort() {' .
				'    return parent::getSeleniumPort();' .
				'  }' .
				'  public function isSeleniumServerRunning() {' .
				'    return parent::isSeleniumServerRunning();' .
				'  }' .
				'  public function runTest() {' .
				'    parent::runTest();' .
				'  }' .
				'}'
			);
		}

		return $className;
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function createAccessibleProxyClassReturnsFixtureSubclassName() {
		$className = $this->createAccessibleProxyClass();

		$this->assertInstanceOf(
			'Tx_Phpunit_Selenium_TestCase',
			new $className(NULL, array(), '', $this->extensionSettingsService)
		);
	}

	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function getSeleniumBrowserUrlForConfiguredBrowserUrlReturnsConfiguredUrl() {
		$url = 'http://example.com/';
		$this->extensionSettingsService->set('selenium_browserurl', $url);

		$this->assertSame(
			$url,
			$this->subject->getSeleniumBrowserUrl()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserUrlForNoConfiguredBrowserUrlReturnsDefaultUrl() {
		$expected = rtrim(
			GeneralUtility::getIndpEnv('TYPO3_SITE_URL'),
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_BROWSER_URL
		);

		$this->assertSame(
			$expected,
			$this->subject->getSeleniumBrowserUrl()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserForConfiguredBrowserReturnsConfiguredBrowser() {
		$browser = '*firefox';
		$this->extensionSettingsService->set('selenium_browser', $browser);

		$this->assertSame(
			$browser,
			$this->subject->getSeleniumBrowser()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserForNoConfiguredBrowserReturnsDefaultBrowser() {
		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_BROWSER,
			$this->subject->getSeleniumBrowser()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumPortForConfiguredPortReturnsConfiguredPort() {
		$port = 1234;
		$this->extensionSettingsService->set('selenium_port', $port);

		$this->assertSame(
			$port,
			$this->subject->getSeleniumPort()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumPortForNoConfiguredPortReturnsDefaultPort() {
		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_PORT,
			$this->subject->getSeleniumPort()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumHostForConfiguredHostReturnsConfiguredHost() {
		$host = 'http://example.com/';
		$this->extensionSettingsService->set('selenium_host', $host);

		$this->assertSame(
			$host,
			$this->subject->getSeleniumHost()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumHostForNotConfiguredHostReturnsTheDefaultHost() {
		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_HOST,
			$this->subject->getSeleniumHost()
		);
	}

	/**
	 * @test
	 */
	public function isSeleniumServerRunningWhenHostIsInvalidReturnsFalse() {
		// We will use 'example.invalid' as an invalid host
		// (according to RFC 2606 the TLD '.invalid' should be used to test for invalid hosts).
		$this->extensionSettingsService->set('selenium_host', 'http://example.invalid');

		$className = $this->createAccessibleProxyClass();
		/** @var $subject Tx_Phpunit_BackEnd_Module */
		$subject = new $className();

		$this->assertFalse(
			$subject->isSeleniumServerRunning()
		);
	}

	/**
	 * @test
	 */
	public function runTestWhenServerIsNotRunningMarksTestAsSkipped() {
		$this->extensionSettingsService->set('selenium_host', 'http://example.invalid');

		$subject = new Tx_Phpunit_Selenium_TestCase();

		try {
			$subject->runTest();
		} catch (PHPUnit_Framework_SkippedTestError $e) {
			$this->assertTrue(TRUE);
		}
	}
}