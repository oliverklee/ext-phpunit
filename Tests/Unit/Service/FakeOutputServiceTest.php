<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FakeOutputServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_FakeOutputService
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_Service_FakeOutputService();
    }

    /**
     * @test
     */
    public function classIsSubclassOfRealOutputService()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_Service_OutputService',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function outputWithNonEmptyStringNotEchosAnything()
    {
        ob_start();
        $this->subject->output('Hello world!');

        self::assertSame(
            '',
            ob_get_contents()
        );

        ob_end_clean();
    }

    /**
     * @test
     */
    public function getCollectedOutputAfterOneOutputCallReturnsOutput()
    {
        $output = 'Hello world!';
        $this->subject->output($output);

        self::assertSame(
            $output,
            $this->subject->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function getCollectedOutputAfterTwoOutputCallReturnsOutputsInCallingOrder()
    {
        $output1 = 'Hello world ...';
        $this->subject->output($output1);
        $output2 = ' and hello again.';
        $this->subject->output($output2);

        self::assertSame(
            $output1 . $output2,
            $this->subject->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function getNumberOfFlushCallsInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfFlushCalls()
        );
    }

    /**
     * @test
     */
    public function getNumberOfFlushCallsAfterOneCallToFlushOutputBufferReturnsOne()
    {
        $this->subject->flushOutputBuffer();

        self::assertSame(
            1,
            $this->subject->getNumberOfFlushCalls()
        );
    }

    /**
     * @test
     */
    public function getNumberOfFlushCallsAfterTwoCallsToFlushOutputBufferReturnsTwo()
    {
        $this->subject->flushOutputBuffer();
        $this->subject->flushOutputBuffer();

        self::assertSame(
            2,
            $this->subject->getNumberOfFlushCalls()
        );
    }
}
