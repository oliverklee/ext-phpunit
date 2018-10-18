<?php

namespace OliverKlee\Phpunit\Tests\Unit\Service;

/**
 * Test case.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class SeleniumServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_SeleniumService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $extensionSettingsService = null;

    protected function setUp()
    {
        $this->extensionSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject = $this->getMock(
            \Tx_Phpunit_Service_SeleniumService::class,
            null,
            [$this->extensionSettingsService]
        );
    }

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

    /**
     * @test
     */
    public function isSeleniumServerRunningWhenHostIsInvalidReturnsFalse()
    {
        // We will use 'example.invalid' as an invalid host
        // (according to RFC 2606 the TLD '.invalid' should be used to test for invalid hosts).
        $this->extensionSettingsService->set('selenium_host', 'http://example.invalid');

        self::assertFalse(
            $this->subject->isSeleniumServerRunning()
        );
    }
}
