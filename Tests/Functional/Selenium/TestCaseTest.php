<?php

namespace OliverKlee\PhpUnit\Tests\Functional\Selenium;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseTest extends \Tx_Phpunit_Selenium_TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @test
     */
    public function exampleSiteHasTitle()
    {
        $this->url('http://www.example.com/');

        self::assertContains('Example', $this->title());
    }
}
