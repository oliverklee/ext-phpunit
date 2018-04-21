<?php
namespace OliverKlee\Phpunit\Tests\Unit\Exception;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseTest extends \Tx_Phpunit_TestCase
{
    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_Database
     *
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function exceptionCanBeThrown()
    {
        throw new \Tx_Phpunit_Exception_Database(1334438897);
    }
}
