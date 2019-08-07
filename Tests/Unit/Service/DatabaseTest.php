<?php

namespace OliverKlee\Phpunit\Tests\Unit\Service;

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseTest extends TestCase
{
    /**
     * @var \Tx_Phpunit_Framework
     */
    private $testingFramework;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->testingFramework = new \Tx_Phpunit_Framework('tx_phpunit');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /*
     * Utility functions
     */

    /**
     * Explodes a comma-separated list of integer values and sorts them
     * numerically.
     *
     * @param string $valueList comma-separated list of values, may be empty
     *
     * @return int[] the separate values, sorted numerically, may be empty
     */
    private function sortExplode($valueList)
    {
        if ($valueList === '') {
            return [];
        }

        $numbers = GeneralUtility::intExplode(',', $valueList);
        sort($numbers, SORT_NUMERIC);

        return $numbers;
    }

    /*
     * Tests for the utility functions
     */

    /**
     * @test
     */
    public function sortExplodeWithEmptyStringReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->sortExplode('')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithOneNumberReturnsArrayWithNumber()
    {
        self::assertSame(
            [42],
            $this->sortExplode('42')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers()
    {
        self::assertSame(
            [1, 2],
            $this->sortExplode('1,2')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers()
    {
        self::assertSame(
            [1, 2],
            $this->sortExplode('2,1')
        );
    }

    /*
     * Tests for enableFields
     */

    /**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooSmallShowHidden()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -2);
    }

    /**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooBigShowHidden()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 2);
    }

    /**
     * @test
     */
    public function enableFieldsIsDifferentForDifferentTables()
    {
        self::assertNotEquals(
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test'),
            \Tx_Phpunit_Service_Database::enableFields('pages')
        );
    }

    /**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenZeroAndOne()
    {
        self::assertNotEquals(
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0),
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsAreTheSameForShowHiddenZeroAndMinusOne()
    {
        self::assertSame(
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0),
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenOneAndMinusOne()
    {
        self::assertNotEquals(
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1),
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsCanBeDifferentForDifferentIgnores()
    {
        self::assertNotEquals(
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0),
            \Tx_Phpunit_Service_Database::enableFields(
                'tx_phpunit_test',
                0,
                ['endtime' => true]
            )
        );
    }

    /**
     * @test
     */
    public function enableFieldsWithHiddenNotAllowedFindsDefaultRecord()
    {
        $this->testingFramework->createRecord('tx_phpunit_test');

        $result = \Tx_Phpunit_Service_Database::selectMultiple(
            '*',
            'tx_phpunit_test',
            '1 = 1' . \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test')
        );

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function enableFieldsWithHiddenAllowedFindsDefaultRecord()
    {
        $this->testingFramework->createRecord('tx_phpunit_test');

        $result = \Tx_Phpunit_Service_Database::selectMultiple(
            '*',
            'tx_phpunit_test',
            '1 = 1' . \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1)
        );

        self::assertCount(1, $result);
    }

    /**
     * @return int[][]
     */
    public function hiddenRecordDataProvider()
    {
        return [
            'hidden' => [['hidden' => 1]],
            'start time in future' => [['starttime' => $GLOBALS['SIM_EXEC_TIME'] + 1000]],
            'end time in past' => [['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]],
        ];
    }

    /**
     * @test
     *
     * @param array $recordData
     *
     * @dataProvider hiddenRecordDataProvider
     */
    public function enableFieldsWithHiddenNotAllowedIgnoresHiddenRecord(array $recordData)
    {
        $this->testingFramework->createRecord('tx_phpunit_test', $recordData);

        $result = \Tx_Phpunit_Service_Database::selectMultiple(
            '*',
            'tx_phpunit_test',
            '1 = 1' . \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test')
        );

        self::assertCount(0, $result);
    }

    /**
     * @test
     *
     * @param array $recordData
     *
     * @dataProvider hiddenRecordDataProvider
     */
    public function enableFieldsWithHiddenAllowedFindsHiddenRecord(array $recordData)
    {
        $this->testingFramework->createRecord('tx_phpunit_test', $recordData);

        $result = \Tx_Phpunit_Service_Database::selectMultiple(
            '*',
            'tx_phpunit_test',
            '1 = 1' . \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1)
        );

        self::assertCount(1, $result);
    }

    /*
     * Tests concerning createRecursivePageList
     */

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion()
    {
        self::assertSame(
            '',
            \Tx_Phpunit_Service_Database::createRecursivePageList('')
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion()
    {
        self::assertSame(
            '',
            \Tx_Phpunit_Service_Database::createRecursivePageList('')
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion()
    {
        self::assertSame(
            '',
            \Tx_Phpunit_Service_Database::createRecursivePageList('', 1)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListThrowsWithNegativeRecursion()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::createRecursivePageList('', -1);
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            (string)$uid,
            \Tx_Phpunit_Service_Database::createRecursivePageList((string)$uid)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion()
    {
        $uid1 = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder($uid1);
        $uid2 = $this->testingFramework->createSystemFolder();

        self::assertSame(
            $this->sortExplode($uid1 . ',' . $uid2),
            $this->sortExplode(
                \Tx_Phpunit_Service_Database::createRecursivePageList($uid1 . ',' . $uid2)
            )
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubsubpagesForRecursionOfOne()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);
        $this->testingFramework->createSystemFolder($subFolderUid);

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainUnrelatedPages()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder();

        self::assertSame(
            (string)$uid,
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainTwoSubpagesOfOnePage()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
        $subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid1 . ',' . $subFolderUid2),
            $this->sortExplode(\Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainSubpagesOfTwoPages()
    {
        $uid1 = $this->testingFramework->createSystemFolder();
        $uid2 = $this->testingFramework->createSystemFolder();
        $subFolderUid1 = $this->testingFramework->createSystemFolder($uid1);
        $subFolderUid2 = $this->testingFramework->createSystemFolder($uid2);

        self::assertSame(
            $this->sortExplode(
                $uid1 . ',' . $uid2 . ',' . $subFolderUid1 . ',' . $subFolderUid2
            ),
            $this->sortExplode(
                \Tx_Phpunit_Service_Database::createRecursivePageList($uid1 . ',' . $uid2, 1)
            )
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            (string)$uid,
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid)
        );
        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
        );
        self::assertSame(
            (string)$uid,
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid)
        );
    }

    /*
     * Tests concerning getColumnsInTable
     */

    /**
     * @test
     */
    public function getColumnsInTableForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::getColumnsInTable('');
    }

    /**
     * @test
     */
    public function getColumnsInTableForInexistentTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_doesnotexist');
    }

    /**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatContainsExistingColumn()
    {
        $columns = \Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_test');

        self::assertTrue(
            isset($columns['title'])
        );
    }

    /**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatNotContainsInexistentColumn()
    {
        $columns = \Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_test');

        self::assertFalse(
            isset($columns['does_not_exist'])
        );
    }

    /*
     * Tests concerning getColumnDefinition
     */

    /**
     * @test
     */
    public function getColumnDefinitionForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::getColumnDefinition('', 'uid');
    }

    /**
     * @test
     */
    public function getColumnDefinitionReturnsArrayThatContainsFieldName()
    {
        $definition = \Tx_Phpunit_Service_Database::getColumnDefinition('tx_phpunit_test', 'title');

        self::assertSame($definition['Field'], 'title');
    }

    /*
     * Tests regarding tableHasColumnUid()
     */

    /**
     * @test
     */
    public function tableHasColumnUidForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::tableHasColumnUid('');
    }

    /**
     * @test
     */
    public function tableHasColumnUidIsTrueOnTableWithColumnUid()
    {
        self::assertTrue(
            \Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function tableHasColumnUidIsFalseOnTableWithoutColumnUid()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test_article_mm')
        );
    }

    /**
     * @test
     */
    public function tableHasColumnUidCanReturnDifferentResultsForDifferentTables()
    {
        self::assertNotEquals(
            \Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test'),
            \Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test_article_mm')
        );
    }

    /*
     * Tests regarding tableHasColumn()
     */

    /**
     * @test
     */
    public function tableHasColumnReturnsTrueOnTableWithColumn()
    {
        self::assertTrue(
            \Tx_Phpunit_Service_Database::tableHasColumn(
                'tx_phpunit_test',
                'title'
            )
        );
    }

    /**
     * @test
     */
    public function tableHasColumnReturnsFalseOnTableWithoutColumn()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::tableHasColumn(
                'tx_phpunit_test',
                'inexistent_column'
            )
        );
    }

    /**
     * @test
     */
    public function tableHasColumnThrowsExceptionOnEmptyTableName()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::tableHasColumn(
            '',
            'title'
        );
    }

    /**
     * @test
     */
    public function tableHasColumnReturnsFalseOnEmptyColumnName()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::tableHasColumn(
                'tx_phpunit_test',
                ''
            )
        );
    }

    /*
     * Tests for delete
     */

    /**
     * @test
     */
    public function deleteForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::delete(
            '',
            'uid = 0'
        );
    }

    /**
     * @test
     */
    public function deleteDeletesRecord()
    {
        $uid = $this->testingFramework->createRecord('tx_phpunit_test');

        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'uid = ' . $uid
        );

        self::assertFalse(
            $this->testingFramework->existsRecordWithUid(
                'tx_phpunit_test',
                $uid
            )
        );
    }

    /**
     * @test
     */
    public function deleteForNoDeletedRecordReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Phpunit_Service_Database::delete(
                'tx_phpunit_test',
                'uid = 0'
            )
        );
    }

    /**
     * @test
     */
    public function deleteForOneDeletedRecordReturnsOne()
    {
        $uid = $this->testingFramework->createRecord('tx_phpunit_test');

        self::assertSame(
            1,
            \Tx_Phpunit_Service_Database::delete(
                'tx_phpunit_test',
                'uid = ' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function deleteForTwoDeletedRecordsReturnsTwo()
    {
        $uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
        $uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

        self::assertSame(
            2,
            \Tx_Phpunit_Service_Database::delete(
                'tx_phpunit_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')'
            )
        );
    }

    /*
     * Tests for update
     */

    /**
     * @test
     */
    public function updateForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::update(
            '',
            'uid = 0',
            []
        );
    }

    /**
     * @test
     */
    public function updateChangesRecord()
    {
        $uid = $this->testingFramework->createRecord('tx_phpunit_test');

        \Tx_Phpunit_Service_Database::update(
            'tx_phpunit_test',
            'uid = ' . $uid,
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_phpunit_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function updateForNoChangedRecordReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Phpunit_Service_Database::update(
                'tx_phpunit_test',
                'uid = 0',
                ['title' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function updateForOneChangedRecordReturnsOne()
    {
        $uid = $this->testingFramework->createRecord('tx_phpunit_test');

        self::assertSame(
            1,
            \Tx_Phpunit_Service_Database::update(
                'tx_phpunit_test',
                'uid = ' . $uid,
                ['title' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function updateForTwoChangedRecordsReturnsTwo()
    {
        $uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
        $uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

        self::assertSame(
            2,
            \Tx_Phpunit_Service_Database::update(
                'tx_phpunit_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')',
                ['title' => 'foo']
            )
        );
    }

    /*
     * Tests for insert
     */

    /**
     * @test
     */
    public function insertForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::insert(
            '',
            ['is_dummy_record' => 1]
        );
    }

    /**
     * @test
     */
    public function insertForEmptyRecordDataThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            []
        );
    }

    /**
     * @test
     */
    public function insertInsertsRecord()
    {
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            [
                'title' => 'foo',
                'is_dummy_record' => 1,
            ]
        );
        $this->testingFramework->markTableAsDirty('tx_phpunit_test');

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_phpunit_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function insertForTableWithUidReturnsUidOfCreatedRecord()
    {
        $uid = \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['is_dummy_record' => 1]
        );
        $this->testingFramework->markTableAsDirty('tx_phpunit_test');

        self::assertTrue(
            $this->testingFramework->existsRecordWithUid(
                'tx_phpunit_test',
                $uid
            )
        );
    }

    /**
     * @test
     */
    public function insertForTableWithoutUidReturnsZero()
    {
        $this->testingFramework->markTableAsDirty('tx_phpunit_test_article_mm');

        self::assertSame(
            0,
            \Tx_Phpunit_Service_Database::insert(
                'tx_phpunit_test_article_mm',
                ['is_dummy_record' => 1]
            )
        );
    }

    /*
     * Tests concerning select, selectSingle, selectMultiple
     */

    /**
     * @test
     */
    public function selectForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::select('*', '');
    }

    /**
     * @test
     */
    public function selectForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::select('', 'tx_phpunit_test');
    }

    /**
     * @test
     */
    public function selectReturnsMySqliResult()
    {
        self::assertInstanceOf(
            'mysqli_result',
            \Tx_Phpunit_Service_Database::select('title', 'tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function selectSingleForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::selectSingle('*', '');
    }

    /**
     * @test
     */
    public function selectSingleForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::selectSingle('', 'tx_phpunit_test');
    }

    /**
     * @test
     */
    public function selectSingleCanFindOneRow()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test'
        );

        self::assertSame(
            ['uid' => (string)$uid],
            \Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function selectSingleForNoResultsThrowsEmptyQueryResultException()
    {
        $this->expectException(\Tx_Phpunit_Exception_EmptyQueryResult::class);

        \Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', 'title = "nothing"');
    }

    /**
     * @test
     */
    public function selectSingleCanOrderTheResults()
    {
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'Title A']
        );
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'Title B']
        );

        self::assertSame(
            ['uid' => (string)$uid],
            \Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', '', '', 'title DESC')
        );
    }

    /**
     * @test
     */
    public function selectSingleCanUseOffset()
    {
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'Title A']
        );
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'Title B']
        );

        self::assertSame(
            ['uid' => (string)$uid],
            \Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', '', '', 'title', 1)
        );
    }

    /**
     * @test
     */
    public function selectMultipleForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::selectMultiple('*', '');
    }

    /**
     * @test
     */
    public function selectMultipleForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::selectMultiple('', 'tx_phpunit_test');
    }

    /**
     * @test
     */
    public function selectMultipleForNoResultsReturnsEmptyArray()
    {
        self::assertSame(
            [],
            \Tx_Phpunit_Service_Database::selectMultiple(
                'uid',
                'tx_phpunit_test',
                'title = "nothing"'
            )
        );
    }

    /**
     * @test
     */
    public function selectMultipleCanFindOneRow()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test'
        );

        self::assertSame(
            [['uid' => (string)$uid]],
            \Tx_Phpunit_Service_Database::selectMultiple('uid', 'tx_phpunit_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function selectMultipleCanFindTwoRows()
    {
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertSame(
            [
                ['title' => 'foo'],
                ['title' => 'foo'],
            ],
            \Tx_Phpunit_Service_Database::selectMultiple(
                'title',
                'tx_phpunit_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForNoMatchesReturnsEmptyArray()
    {
        self::assertSame(
            [],
            \Tx_Phpunit_Service_Database::selectColumnForMultiple(
                'title',
                'tx_phpunit_test',
                'title = "nothing"'
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForOneMatchReturnsArrayWithColumnContent()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertSame(
            ['foo'],
            \Tx_Phpunit_Service_Database::selectColumnForMultiple(
                'title',
                'tx_phpunit_test',
                'uid = ' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForTwoMatchReturnsArrayWithColumnContents()
    {
        $uid1 = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $uid2 = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'bar']
        );

        $result = \Tx_Phpunit_Service_Database::selectColumnForMultiple(
            'title',
            'tx_phpunit_test',
            'uid = ' . $uid1 . ' OR uid = ' . $uid2
        );
        sort($result);
        self::assertSame(
            ['bar', 'foo'],
            $result
        );
    }

    /*
     * Tests concerning getAllTableNames
     */

    /**
     * @test
     */
    public function getAllTableNamesContainsExistingTable()
    {
        self::assertContains(
            'tx_phpunit_test',
            \Tx_Phpunit_Service_Database::getAllTableNames()
        );
    }

    /**
     * @test
     */
    public function getAllTableNamesNotContainsInexistentTable()
    {
        self::assertNotContains(
            'tx_phpunit_doesnotexist',
            \Tx_Phpunit_Service_Database::getAllTableNames()
        );
    }

    /*
     * Tests concerning existsTable
     */

    /**
     * @test
     */
    public function existsTableWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsTable('');
    }

    /**
     * @test
     */
    public function existsTableForExistingTableReturnsTrue()
    {
        self::assertTrue(
            \Tx_Phpunit_Service_Database::existsTable('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function existsTableForInexistentTableReturnsFalse()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsTable('tx_phpunit_doesnotexist')
        );
    }

    /*
     * Tests concerning getTcaForTable
     */

    /**
     * @test
     */
    public function getTcaForTableReturnsValidTcaArray()
    {
        $tca = \Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_test');

        self::assertInternalType('array', $tca['ctrl']);
        self::assertInternalType('array', $tca['interface']);
        self::assertInternalType('array', $tca['columns']);
        self::assertInternalType('array', $tca['types']);
        self::assertInternalType('array', $tca['palettes']);
    }

    /**
     * @test
     */
    public function getTcaForTableWithEmptyTableNameThrowsExceptionTca()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::getTcaForTable('');
    }

    /**
     * @test
     */
    public function getTcaForTableWithInexistentTableNameThrowsExceptionTca()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_doesnotexist');
    }

    /**
     * @test
     */
    public function getTcaForTableThrowsExceptionOnTableWithoutTca()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_test_article_mm');
    }

    /**
     * @test
     */
    public function getTcaForTableCanLoadFieldsAddedByExtensions()
    {
        $tca = \Tx_Phpunit_Service_Database::getTcaForTable('fe_users');

        self::assertTrue(
            isset($tca['columns']['tx_phpunit_is_dummy_record'])
        );
    }

    /*
     * Tests concerning count
     */

    /**
     * @test
     */
    public function countCanBeCalledWithEmptyWhereClause()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithMissingWhereClause()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function countForNoMatchesReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Phpunit_Service_Database::count(
                'tx_phpunit_test',
                'uid = 42'
            )
        );
    }

    /**
     * @test
     */
    public function countForOneMatchReturnsOne()
    {
        self::assertSame(
            1,
            \Tx_Phpunit_Service_Database::count(
                'tx_phpunit_test',
                'uid = ' . $this->testingFramework->createRecord('tx_phpunit_test')
            )
        );
    }

    /**
     * @test
     */
    public function countForTwoMatchesReturnsTwo()
    {
        $uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
        $uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

        self::assertSame(
            2,
            \Tx_Phpunit_Service_Database::count(
                'tx_phpunit_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')'
            )
        );
    }

    /**
     * @test
     */
    public function countCanBeCalledForTableWithoutUid()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test_article_mm');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithMultipleTables()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test, tx_phpunit_testchild');
    }

    /**
     * @test
     */
    public function countWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::count('tx_phpunit_doesnotexist', 'uid = 42');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithJoinedTables()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test JOIN tx_phpunit_testchild');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithoutTables()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::count('JOIN');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::count('tx_phpunit_test JOIN ');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::count('JOIN tx_phpunit_test');
    }

    /*
     * Tests regarding existsRecord
     */

    /**
     * @test
     */
    public function existsRecordWithEmptyWhereClauseIsAllowed()
    {
        \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function existsRecordWithMissingWhereClauseIsAllowed()
    {
        \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function existsRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsRecord('');
    }

    /**
     * @test
     */
    public function existsRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_doesnotexist');
    }

    /**
     * @test
     */
    public function existsRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'uid = 42')
        );
    }

    /**
     * @test
     */
    public function existsRecordForOneMatchReturnsTrue()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test'
        );

        self::assertTrue(
            \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue()
    {
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /*
     * Tests regarding existsExactlyOneRecord
     */

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed()
    {
        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed()
    {
        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_doesnotexist');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'uid = 42')
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForOneMatchReturnsTrue()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test'
        );

        self::assertTrue(
            \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForTwoMatchesReturnsFalse()
    {
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /*
     * Tests regarding existsRecordWithUid
     */

    /**
     * @test
     */
    public function existsRecordWithUidWithZeroUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', 0);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithNegativeUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', -1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Phpunit_Service_Database::existsRecordWithUid('', 42);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_doesnotexist', 42);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatchReturnsFalse()
    {
        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', 42)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidForMatchReturnsTrue()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test'
        );

        self::assertTrue(
            \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidUsesAdditionalNonEmptyWhereClause()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_phpunit_test',
            ['deleted' => 1]
        );

        self::assertFalse(
            \Tx_Phpunit_Service_Database::existsRecordWithUid(
                'tx_phpunit_test',
                $uid,
                ' AND deleted = 0'
            )
        );
    }

    /**
     * @test
     */
    public function getDatabaseConnectionReturnsGlobalsDatabaseConnection()
    {
        self::assertSame(
            $GLOBALS['TYPO3_DB'],
            \Tx_Phpunit_Service_Database::getDatabaseConnection()
        );
    }
}
