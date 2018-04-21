<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OutputServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_OutputService
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_Service_OutputService();
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(
            SingletonInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function outputOutputsOutput()
    {
        $output = 'Hello world!';

        ob_start();
        $this->subject->output($output);

        self::assertSame(
            $output,
            ob_get_contents()
        );

        ob_end_clean();
    }
}
