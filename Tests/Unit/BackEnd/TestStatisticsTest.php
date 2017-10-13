<?php
namespace OliverKlee\Phpunit\Tests\Unit\BackEnd;

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

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestStatisticsTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_BackEnd_TestStatistics
     */
    protected $subject = null;

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $this->subject = new \Tx_Phpunit_BackEnd_TestStatistics();
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function startCalledTwoTimesThrowsException()
    {
        $this->subject->start();
        $this->subject->start();
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function stopWithoutStartThrowsException()
    {
        $this->subject->stop();
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function stopCalledTwoTimesAfterStartThrowsException()
    {
        $this->subject->start();
        $this->subject->stop();
        $this->subject->stop();
    }

    /**
     * @test
     */
    public function getTimeInitiallyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeWithoutStartAfterPauseReturnsZero()
    {
        usleep(100000);

        self::assertSame(
            0.0,
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeAfterStartAfterPauseReturnsPassedTime()
    {
        $this->subject->start();
        usleep(100000);

        self::assertEquals(
            0.1,
            $this->subject->getTime(),
            '',
            0.09
        );
    }

    /**
     * @test
     */
    public function getTimeAfterStartAndStopReturnsPauseBeforeStop()
    {
        $this->subject->start();
        usleep(100000);
        $this->subject->stop();

        self::assertEquals(
            0.1,
            $this->subject->getTime(),
            '',
            0.09
        );
    }
}
