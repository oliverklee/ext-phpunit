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
     */
    public function exceptionCanBeThrown()
    {
        $this->expectException(\Tx_Phpunit_Exception_Database::class);

        throw new \Tx_Phpunit_Exception_Database(1334438897);
    }
}
