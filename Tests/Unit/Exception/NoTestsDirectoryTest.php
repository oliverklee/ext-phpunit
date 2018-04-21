<?php
namespace OliverKlee\Phpunit\Tests\Unit\Exception;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class NoTestsDirectoryTest extends \Tx_Phpunit_TestCase
{
    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_NoTestsDirectory
     *
     * @throws \Tx_Phpunit_Exception_NoTestsDirectory
     */
    public function exceptionCanBeThrown()
    {
        throw new \Tx_Phpunit_Exception_NoTestsDirectory('some message', 12345);
    }
}
