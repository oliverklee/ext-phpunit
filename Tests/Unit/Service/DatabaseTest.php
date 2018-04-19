<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseTest extends \Tx_PhpUnit_TestCase
{
    /**
     * @var \Tx_Phpunit_Framework
     */
    private $testingFramework;

    protected function setUp()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function enableFieldsThrowsExceptionForTooSmallShowHidden()
    {
        \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -2);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function enableFieldsThrowsExceptionForTooBigShowHidden()
    {
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
            \Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0, []),
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
            \Tx_Phpunit_Service_Database::createRecursivePageList('', 0)
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRecursivePageListThrowsWithNegativeRecursion()
    {
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
            \Tx_Phpunit_Service_Database::createRecursivePageList((string)$uid, 0)
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
                \Tx_Phpunit_Service_Database::createRecursivePageList($uid1 . ',' . $uid2, 0)
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
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
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
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
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
            \Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
        );
    }

    /*
     * Tests concerning getColumnsInTable
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getColumnsInTableForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::getColumnsInTable('');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function getColumnsInTableForInexistentTableNameThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function getColumnDefinitionForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::getColumnDefinition('', 'uid');
    }

    /**
     * @test
     */
    public function getColumnDefinitionReturnsArrayThatContainsFieldName()
    {
        $definition = \Tx_Phpunit_Service_Database::getColumnDefinition('tx_phpunit_test', 'title');

        self::assertTrue(
            $definition['Field'] === 'title'
        );
    }

    /*
     * Tests regarding tableHasColumnUid()
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function tableHasColumnUidForEmptyTableNameThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function tableHasColumnThrowsExceptionOnEmptyTableName()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteForEmptyTableNameThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function updateForEmptyTableNameThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function insertForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::insert(
            '',
            ['is_dummy_record' => 1]
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function insertForEmptyRecordDataThrowsException()
    {
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
            ['title' => 'foo',
            'is_dummy_record' => 1, ]
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::select('*', '');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectForEmptyFieldListThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectSingleForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::selectSingle('*', '');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectSingleForEmptyFieldListThrowsException()
    {
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
     *
     * @expectedException \Tx_Phpunit_Exception_EmptyQueryResult
     */
    public function selectSingleForNoResultsThrowsEmptyQueryResultException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectMultipleForEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::selectMultiple('*', '');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function selectMultipleForEmptyFieldListThrowsException()
    {
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
        self::assertTrue(
            in_array('tx_phpunit_test', \Tx_Phpunit_Service_Database::getAllTableNames())
        );
    }

    /**
     * @test
     */
    public function getAllTableNamesNotContainsInexistentTable()
    {
        self::assertFalse(
            in_array('tx_phpunit_doesnotexist', \Tx_Phpunit_Service_Database::getAllTableNames())
        );
    }

    /*
     * Tests concerning existsTable
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsTableWithEmptyTableNameThrowsException()
    {
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

        self::assertTrue(is_array($tca['ctrl']));
        self::assertTrue(is_array($tca['interface']));
        self::assertTrue(is_array($tca['columns']));
        self::assertTrue(is_array($tca['types']));
        self::assertTrue(is_array($tca['palettes']));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getTcaForTableWithEmptyTableNameThrowsExceptionTca()
    {
        \Tx_Phpunit_Service_Database::getTcaForTable('');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function getTcaForTableWithInexistentTableNameThrowsExceptionTca()
    {
        \Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_doesnotexist');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function getTcaForTableThrowsExceptionOnTableWithoutTca()
    {
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
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test', '');
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
     *
     * @expectedException \BadMethodCallException
     */
    public function countWithInvalidTableNameThrowsException()
    {
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
     *
     * @expectedException \BadMethodCallException
     */
    public function countDoesNotAllowJoinWithoutTables()
    {
        \Tx_Phpunit_Service_Database::count('JOIN');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft()
    {
        \Tx_Phpunit_Service_Database::count('tx_phpunit_test JOIN ');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight()
    {
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
        \Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', '');
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::existsRecord('');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function existsRecordWithInvalidTableNameThrowsException()
    {
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
        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', '');
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::existsExactlyOneRecord('');
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException()
    {
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
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithZeroUidThrowsException()
    {
        \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', 0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithNegativeUidThrowsException()
    {
        \Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', -1);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException()
    {
        \Tx_Phpunit_Service_Database::existsRecordWithUid('', 42);
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException()
    {
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
