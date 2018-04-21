<?php
namespace OliverKlee\Phpunit\Tests\Unit\Exception;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EmptyQueryResultTest extends \Tx_Phpunit_TestCase
{
    /**
     * the saved content of $GLOBALS['TYPO3_DB']->debugOutput
     *
     * @var bool
     */
    private $savedDebugOutput;

    /**
     * the saved content of $GLOBALS['TYPO3_DB']->store_lastBuiltQuery
     *
     * @var bool
     */
    private $savedStoreLastBuildQuery;

    protected function setUp()
    {
        $databaseConnection = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        $this->savedDebugOutput = $databaseConnection->debugOutput;
        $this->savedStoreLastBuildQuery = $databaseConnection->store_lastBuiltQuery;

        $databaseConnection->debugOutput = false;
        $databaseConnection->store_lastBuiltQuery = true;
    }

    protected function tearDown()
    {
        $databaseConnection = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        $databaseConnection->debugOutput = $this->savedDebugOutput;
        $databaseConnection->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_EmptyQueryResult
     *
     * @throws \Tx_Phpunit_Exception_EmptyQueryResult
     */
    public function exceptionCanBeThrown()
    {
        throw new \Tx_Phpunit_Exception_EmptyQueryResult(1334438911);
    }

    /**
     * @test
     */
    public function messageAfterQueryWithLastQueryEnabledContainsLastQuery()
    {
        \Tx_Phpunit_Service_Database::getDatabaseConnection()->exec_SELECTquery('title', 'tx_phpunit_test', '');
        $subject = new \Tx_Phpunit_Exception_EmptyQueryResult();

        self::assertContains(
            'SELECT',
            $subject->getMessage()
        );
    }
}
