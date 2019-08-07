<?php

namespace OliverKlee\Phpunit\Tests\Unit\Selenium;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Carsten Koenig <ck@carsten-koenig.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseTest extends TestCase
{
    /**
     * @var \Tx_Phpunit_Selenium_TestCase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $extensionSettingsService = null;

    /**
     * @var \Tx_Phpunit_Service_SeleniumService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $seleniumService = null;

    protected function setUp()
    {
        if (!class_exists('PHPUnit_Extensions_Selenium2TestCase')) {
            self::markTestSkipped('PHPUnit Selenium is not installed.');
        }

        $this->extensionSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->seleniumService = $this->getMockBuilder(\Tx_Phpunit_Service_SeleniumService::class)
            ->setMethods(null)->setConstructorArgs([$this->extensionSettingsService])->getMock();
        $this->subject = $this->getMockBuilder($this->createAccessibleProxyClass())
            ->setMethods(['isSeleniumServerRunning'])
            ->setConstructorArgs([null, [], '', $this->extensionSettingsService, $this->seleniumService])
            ->getMock();
        $this->subject->method('isSeleniumServerRunning')->willReturn(true);
    }

    /*
     * Utility functions
     */

    /**
     * This function creates a subclass of \Tx_Phpunit_Selenium_TestCase with
     * some attributes and methods made public.
     *
     * @return string class name, will not be empty
     */
    private function createAccessibleProxyClass()
    {
        $className = uniqid('Tx_Phpunit_Selenium_AccessibleTestCase');

        if (!class_exists($className, false)) {
            eval(
                'class ' . $className . ' extends \\Tx_Phpunit_Selenium_TestCase {' .
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
    public function createAccessibleProxyClassReturnsFixtureSubclassName()
    {
        $className = $this->createAccessibleProxyClass();

        self::assertInstanceOf(
            'Tx_Phpunit_Selenium_TestCase',
            new $className(null, [], '', $this->extensionSettingsService)
        );
    }

    /*
     * Unit tests
     */

    /**
     * @test
     */
    public function getSeleniumBrowserUrlForConfiguredBrowserUrlReturnsConfiguredUrl()
    {
        $url = 'http://example.com/';
        $this->extensionSettingsService->set('selenium_browserurl', $url);

        self::assertSame(
            $url,
            $this->subject->getSeleniumBrowserUrl()
        );
    }

    /**
     * @test
     */
    public function getSeleniumBrowserForConfiguredBrowserReturnsConfiguredBrowser()
    {
        $browser = '*mock';
        $this->extensionSettingsService->set('selenium_browser', $browser);

        self::assertSame(
            $browser,
            $this->subject->getSeleniumBrowser()
        );
    }

    /**
     * @test
     */
    public function getSeleniumPortForConfiguredPortReturnsConfiguredPort()
    {
        $port = 1234;
        $this->extensionSettingsService->set('selenium_port', $port);

        self::assertSame(
            $port,
            $this->subject->getSeleniumPort()
        );
    }

    /**
     * @test
     */
    public function getSeleniumHostForConfiguredHostReturnsConfiguredHost()
    {
        $host = 'http://example.com/';
        $this->extensionSettingsService->set('selenium_host', $host);

        self::assertSame(
            $host,
            $this->subject->getSeleniumHost()
        );
    }
}
