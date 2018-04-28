<?php
namespace OliverKlee\Phpunit\Tests\Unit\BackEnd;

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
     */
    public function startCalledTwoTimesThrowsException()
    {
        $this->subject->start();

        $this->expectException(\BadMethodCallException::class);

        $this->subject->start();
    }

    /**
     * @test
     */
    public function stopWithoutStartThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->stop();
    }

    /**
     * @test
     */
    public function stopCalledTwoTimesAfterStartThrowsException()
    {
        $this->subject->start();
        $this->subject->stop();

        $this->expectException(\BadMethodCallException::class);

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
