<?php
namespace OliverKlee\Phpunit\Tests\Functional\Selenium;

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
