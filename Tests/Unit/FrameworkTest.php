<?php
namespace OliverKlee\Phpunit\Tests\Unit;

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

use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class FrameworkTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Framework
     */
    protected $subject = null;

    /**
     * absolute path to a "foreign" folder which was created for test purposes
     * and which should be deleted in tearDown(); this is needed for
     * deleteDummyFolderWithForeignFolderThrowsException
     *
     * @var string
     */
    private $foreignFolderToDelete = '';

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array
     */
    private $extConfBackup = [];

    /**
     * backed-up T3_VAR configuration
     *
     * @var array
     */
    private $t3VarBackup = [];

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

        $this->subject = new \Tx_Phpunit_Framework('tx_phpunit', ['user_phpunittest']);
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;

        $this->subject->setResetAutoIncrementThreshold(1);
        $this->subject->purgeHooks();
        $this->subject->cleanUp();
        $this->deleteForeignFolder();
    }

    // ---------------------------------------------------------------------
    // Utility functions.
    // ---------------------------------------------------------------------

    /**
     * Returns the sorting value of the relation between the local UID given by
     * the first parameter $uidLocal and the foreign UID given by the second
     * parameter $uidForeign.
     *
     * @param int $uidLocal
     *        the UID of the local record, must be > 0
     * @param int $uidForeign
     *        the UID of the foreign record, must be > 0
     *
     * @return int the sorting value of the relation
     */
    private function getSortingOfRelation($uidLocal, $uidForeign)
    {
        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'sorting',
            'tx_phpunit_test_article_mm',
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
        );

        return (int)$row['sorting'];
    }

    /**
     * Checks whether the extension user_phpunittest is currently loaded and lets
     * a test fail if the extension is not loaded.
     *
     * @return void
     */
    private function checkIfExtensionUserPhpUnittestIsLoaded()
    {
        if (!ExtensionManagementUtility::isLoaded('user_phpunittest')) {
            self::markTestSkipped(
                'The Extension user_phpunittest is not installed, but needs to be installed. ' .
                'Please install it from EXT:phpunit/TestExtensions/user_phpunittest/.'
            );
        }
    }

    /**
     * Checks whether the extension user_phpunittest2 is currently loaded and lets
     * a test fail if the extension is not loaded.
     *
     * @return void
     */
    private function checkIfExtensionUserPhpUnittest2IsLoaded()
    {
        if (!ExtensionManagementUtility::isLoaded('user_phpunittest')) {
            self::markTestSkipped(
                'THe extension user_phpunittest2 is not installed, but needs to be installed. ' .
                'Please install it from EXT:phpunit/TestExtensions/user_phpunittest2/.'
            );
        }
    }

    /**
     * Deletes a "foreign" folder which was created for test purposes.
     *
     * @return void
     */
    private function deleteForeignFolder()
    {
        if ($this->foreignFolderToDelete == '') {
            return;
        }

        GeneralUtility::rmdir($this->foreignFolderToDelete);
        $this->foreignFolderToDelete = '';
    }

    /**
     * Marks a test as skipped if the ZIPArchive class is not available in the
     * PHP installation.
     *
     * @return void
     */
    private function markAsSkippedForNoZipArchive()
    {
        try {
            $this->subject->checkForZipArchive();
        } catch (Exception $exception) {
            self::markTestSkipped($exception->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Tests regarding markTableAsDirty()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function markTableAsDirty()
    {
        self::assertSame(
            [],
            $this->subject->getListOfDirtyTables()
        );

        $this->subject->createRecord('tx_phpunit_test', []);
        self::assertSame(
            [
                'tx_phpunit_test' => 'tx_phpunit_test',
            ],
            $this->subject->getListOfDirtyTables()
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpNonSystemTable()
    {
        $uid = \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('tx_phpunit_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('tx_phpunit_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpSystemTable()
    {
        $uid = \Tx_Phpunit_Service_Database::insert(
            'pages',
            ['tx_phpunit_is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('pages');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('pages', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpAdditionalAllowedTable()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $uid = \Tx_Phpunit_Service_Database::insert(
            'user_phpunittest_test',
            ['tx_phpunit_is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('user_phpunittest_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('user_phpunittest_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnInexistentTable()
    {
        $this->subject->markTableAsDirty('tx_phpunit_DOESNOTEXIST');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnNotAllowedSystemTable()
    {
        $this->subject->markTableAsDirty('sys_domain');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnForeignTable()
    {
        $this->subject->markTableAsDirty('tx_seminars_seminars');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function markTableAsDirtyFailsWithEmptyTableName()
    {
        $this->subject->markTableAsDirty('');
    }

    /**
     * @test
     */
    public function markTableAsDirtyAcceptsCommaSeparatedListOfTableNames()
    {
        $this->subject->markTableAsDirty('tx_phpunit_test,tx_phpunit_test_article_mm');
        self::assertSame(
            [
                'tx_phpunit_test' => 'tx_phpunit_test',
                'tx_phpunit_test_article_mm' => 'tx_phpunit_test_article_mm',
            ],
            $this->subject->getListOfDirtyTables()
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding createRecord()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createRecordOnValidTableWithNoData()
    {
        self::assertNotEquals(
            0,
            $this->subject->createRecord('tx_phpunit_test', [])
        );
    }

    /**
     * @test
     */
    public function createRecordWithValidData()
    {
        $title = 'TEST record';
        $uid = $this->subject->createRecord(
            'tx_phpunit_test',
            [
                'title' => $title,
            ]
        );
        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'tx_phpunit_test',
            'uid = ' . $uid
        );

        self::assertSame(
            $title,
            $row['title']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRecordOnInvalidTable()
    {
        $this->subject->createRecord('tx_phpunit_DOESNOTEXIST', []);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRecordWithEmptyTableName()
    {
        $this->subject->createRecord('', []);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRecordWithUidFails()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['uid' => 99999]
        );
    }

    /**
     * @test
     */
    public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $title = 'TEST record';
        $this->subject->createRecord(
            'user_phpunittest_test',
            [
                'title' => $title,
            ]
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding changeRecord()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function changeRecordWithExistingRecord()
    {
        $uid = $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        $this->subject->changeRecord(
            'tx_phpunit_test',
            $uid,
            ['title' => 'bar']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'tx_phpunit_test',
            'uid = ' . $uid
        );

        self::assertSame(
            'bar',
            $row['title']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsOnForeignTable()
    {
        $this->subject->changeRecord(
            'tx_seminars_seminars',
            99999,
            ['title' => 'foo']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsOnInexistentTable()
    {
        $this->subject->changeRecord(
            'tx_phpunit_DOESNOTEXIST',
            99999,
            ['title' => 'foo']
        );
    }

    /**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForPages()
    {
        $pid = $this->subject->createFrontEndPage(0, ['title' => 'foo']);

        $this->subject->changeRecord(
            'pages',
            $pid,
            ['title' => 'bar']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('pages', 'uid=' . $pid . ' AND title="bar"')
        );
    }

    /**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForContent()
    {
        $pid = $this->subject->createFrontEndPage(0, ['title' => 'foo']);
        $uid = $this->subject->createContentElement(
            $pid,
            ['header' => 'foo']
        );

        $this->subject->changeRecord(
            'tt_content',
            $uid,
            ['header' => 'bar']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('tt_content', 'uid=' . $uid . ' AND header=\'bar\'')
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsOnOtherSystemTable()
    {
        $this->subject->changeRecord(
            'sys_domain',
            1,
            ['title' => 'bar']
        );
    }

    /**
     * @test
     */
    public function changeRecordOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $uid = $this->subject->createRecord(
            'user_phpunittest_test',
            ['title' => 'foo']
        );

        $this->subject->changeRecord(
            'user_phpunittest_test',
            $uid,
            ['title' => 'bar']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsWithUidZero()
    {
        $this->subject->changeRecord('tx_phpunit_test', 0, ['title' => 'foo']);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsWithEmptyData()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test', []);

        $this->subject->changeRecord(
            'tx_phpunit_test',
            $uid,
            []
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsWithUidFieldInRecordData()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test', []);

        $this->subject->changeRecord(
            'tx_phpunit_test',
            $uid,
            ['uid' => '55742']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function changeRecordFailsWithDummyRecordFieldInRecordData()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test', []);

        $this->subject->changeRecord(
            'tx_phpunit_test',
            $uid,
            ['is_dummy_record' => 0]
        );
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_Database
     */
    public function changeRecordFailsOnInexistentRecord()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test', []);

        $this->subject->changeRecord(
            'tx_phpunit_test',
            $uid + 1,
            ['title' => 'foo']
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding deleteRecord()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function deleteRecordOnValidDummyRecord()
    {
        // Creates and directly destroys a dummy record.
        $uid = $this->subject->createRecord('tx_phpunit_test', []);
        $this->subject->deleteRecord('tx_phpunit_test', $uid);

        // Checks whether the record really was removed from the database.
        self::assertSame(
            0,
            $this->subject->countRecords('tx_phpunit_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function deleteRecordOnValidDummyRecordOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        // Creates and directly destroys a dummy record.
        $uid = $this->subject->createRecord('user_phpunittest_test', []);
        $this->subject->deleteRecord('user_phpunittest_test', $uid);
    }

    /**
     * @test
     */
    public function deleteRecordOnInexistentRecord()
    {
        $uid = 99999;

        // Checks that the record is inexistent before testing on it.
        self::assertSame(
            0,
            $this->subject->countRecords('tx_phpunit_test', 'uid=' . $uid)
        );

        // Runs our delete function - it should run through even when it cannot
        // delete a record.
        $this->subject->deleteRecord('tx_phpunit_test', $uid);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteRecordOnForeignTable()
    {
        $table = 'tx_seminars_seminars';
        $uid = 99999;

        $this->subject->deleteRecord($table, $uid);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteRecordOnInexistentTable()
    {
        $table = 'tx_phpunit_DOESNOTEXIST';
        $uid = 99999;

        $this->subject->deleteRecord($table, $uid);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteRecordWithEmptyTableName()
    {
        $table = '';
        $uid = 99999;

        $this->subject->deleteRecord($table, $uid);
    }

    /**
     * @test
     */
    public function deleteRecordOnNonTestRecordNotDeletesRecord()
    {
        // Create a new record that looks like a real record, i.e. the
        // is_dummy_record flag is set to 0.
        $uid = \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            [
                'title' => 'TEST',
                'is_dummy_record' => 0,
            ]
        );

        // Runs our delete method which should NOT affect the record created
        // above.
        $this->subject->deleteRecord('tx_phpunit_test', $uid);

        // Remembers whether the record still exists.
        $counter = \Tx_Phpunit_Service_Database::count('tx_phpunit_test', 'uid = ' . $uid);

        // Deletes the record as it will not be caught by the clean up function.
        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'uid = ' . $uid . ' AND is_dummy_record = 0'
        );

        // Checks whether the record still had existed.
        self::assertSame(
            1,
            $counter
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding createRelation()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createRelationWithValidData()
    {
        $uidLocal = $this->subject->createRecord('tx_phpunit_test');
        $uidForeign = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );

        // Checks whether the record really exists.
        self::assertSame(
            1,
            $this->subject->countRecords(
                'tx_phpunit_test_article_mm',
                'uid_local=' . $uidLocal . ' AND uid_foreign=' . $uidForeign
            )
        );
    }

    /**
     * @test
     */
    public function createRelationWithValidDataOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $uidLocal = $this->subject->createRecord('user_phpunittest_test');
        $uidForeign = $this->subject->createRecord('user_phpunittest_test');

        $this->subject->createRelation(
            'user_phpunittest_test_article_mm',
            $uidLocal,
            $uidForeign
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithInvalidTable()
    {
        $table = 'tx_phpunit_test_DOESNOTEXIST_mm';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->subject->createRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithEmptyTableName()
    {
        $this->subject->createRelation('', 99999, 199999);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithZeroFirstUid()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation('tx_phpunit_test_article_mm', 0, $uid);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithZeroSecondUid()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation('tx_phpunit_test_article_mm', $uid, 0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithNegativeFirstUid()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation('tx_phpunit_test_article_mm', -1, $uid);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createRelationWithNegativeSecondUid()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation('tx_phpunit_test_article_mm', $uid, -1);
    }

    /**
     * @test
     */
    public function createRelationWithAutomaticSorting()
    {
        $uidLocal = $this->subject->createRecord('tx_phpunit_test');
        $uidForeign = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertGreaterThan(
            0,
            $previousSorting
        );

        $uidForeign = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->createRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $nextSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertSame(
            ($previousSorting + 1),
            $nextSorting
        );
    }

    /**
     * @test
     */
    public function createRelationWithManualSorting()
    {
        $uidLocal = $this->subject->createRecord('tx_phpunit_test');
        $uidForeign = $this->subject->createRecord('tx_phpunit_test');
        $sorting = 99999;

        $this->subject->createRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign,
            $sorting
        );

        self::assertSame(
            $sorting,
            $this->getSortingOfRelation($uidLocal, $uidForeign)
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding createRelationFromTca()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne()
    {
        $firstRecordUid = $this->subject->createRecord('tx_phpunit_test');
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'related_records',
            'tx_phpunit_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            1,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne()
    {
        $firstRecordUid = $this->subject->createRecord(
            'tx_phpunit_test',
            ['related_records' => 1]
        );
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'related_records',
            'tx_phpunit_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            2,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterCreatesRecordInRelationTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_phpunit_test');
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $count = $this->subject->countRecords(
            'tx_phpunit_test_article_mm',
            'uid_local=' . $firstRecordUid
        );
        self::assertSame(
            1,
            $count
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter()
    {
        $firstRecordUid = $this->subject->createRecord('tx_phpunit_test');
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'bidirectional',
            'tx_phpunit_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            1,
            (int)$row['bidirectional']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesOppositeFieldCounterInForeignTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_phpunit_test');
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'related_records',
            'tx_phpunit_test',
            'uid = ' . $secondRecordUid
        );

        self::assertSame(
            1,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_phpunit_test');
        $secondRecordUid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_phpunit_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $count = $this->subject->countRecords(
            'tx_phpunit_test_article_mm',
            'uid_local=' . $secondRecordUid . ' AND uid_foreign=' . $firstRecordUid
        );
        self::assertSame(
            1,
            $count
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding removeRelation()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function removeRelationOnValidDummyRecord()
    {
        $uidLocal = $this->subject->createRecord('tx_phpunit_test');
        $uidForeign = $this->subject->createRecord('tx_phpunit_test');

        // Creates and directly destroys a dummy record.
        $this->subject->createRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $this->subject->removeRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );

        // Checks whether the record really was removed from the database.
        self::assertSame(
            0,
            $this->subject->countRecords(
                'tx_phpunit_test_article_mm',
                'uid_local=' . $uidLocal . ' AND uid_foreign=' . $uidForeign
            )
        );
    }

    /**
     * @test
     */
    public function removeRelationOnValidDummyRecordOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $uidLocal = $this->subject->createRecord('user_phpunittest_test');
        $uidForeign = $this->subject->createRecord('user_phpunittest_test');

        // Creates and directly destroys a dummy record.
        $this->subject->createRelation(
            'user_phpunittest_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $this->subject->removeRelation(
            'user_phpunittest_test_article_mm',
            $uidLocal,
            $uidForeign
        );
    }

    /**
     * @test
     */
    public function removeRelationOnInexistentRecord()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $uidLocal = $uid + 1;
        $uidForeign = $uid + 2;

        // Checks that the record is inexistent before testing on it.
        self::assertSame(
            0,
            $this->subject->countRecords(
                'tx_phpunit_test_article_mm',
                'uid_local=' . $uidLocal . ' AND uid_foreign=' . $uidForeign
            )
        );

        // Runs our delete function - it should run through even when it cannot
        // delete a record.
        $this->subject->removeRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function removeRelationOnForeignTable()
    {
        $table = 'tx_seminars_seminars_places_mm';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->subject->removeRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function removeRelationOnInexistentTable()
    {
        $table = 'tx_phpunit_DOESNOTEXIST';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->subject->removeRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function removeRelationWithEmptyTableName()
    {
        $table = '';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->subject->removeRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     */
    public function removeRelationOnRealRecordNotRemovesRelation()
    {
        $uidLocal = $this->subject->createRecord('tx_phpunit_test');
        $uidForeign = $this->subject->createRecord('tx_phpunit_test');

        // Create a new record that looks like a real record, i.e. the
        // is_dummy_record flag is set to 0.
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test_article_mm',
            [
                'uid_local' => $uidLocal,
                'uid_foreign' => $uidForeign,
                'is_dummy_record' => 0,
            ]
        );

        // Runs our delete method which should NOT affect the record created
        // above.
        $this->subject->removeRelation(
            'tx_phpunit_test_article_mm',
            $uidLocal,
            $uidForeign
        );

        // Caches the value that will be tested for later. We need to use the
        // following order to make sure the test record gets deleted even if
        // this test fails:
        // 1. reads the value to test
        // 2. deletes the test record
        // 3. tests the previously read value (and possibly fails)
        $numberOfCreatedRelations = \Tx_Phpunit_Service_Database::count(
            'tx_phpunit_test_article_mm',
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
        );

        // Deletes the record as it will not be caught by the clean up function.
        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test_article_mm',
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign . ' AND is_dummy_record = 0'
        );

        // Checks whether the relation had been created further up.
        self::assertSame(
            1,
            $numberOfCreatedRelations
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding cleanUp()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function cleanUpWithRegularCleanUp()
    {
        // Creates a dummy record (and marks that table as dirty).
        $this->subject->createRecord('tx_phpunit_test');

        // Creates a dummy record directly in the database, without putting this
        // table name to the list of dirty tables.
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test_article_mm',
            ['is_dummy_record' => 1]
        );

        // Runs a regular clean up. This should now delete only the first record
        // which was created through the testing framework and thus that table
        // is on the list of dirty tables. The second record was directly put
        // into the database and it's table is not on this list and will not be
        // removed by a regular clean up run.
        $this->subject->cleanUp();

        // Checks whether the first dummy record is deleted.
        self::assertSame(
            0,
            $this->subject->countRecords('tx_phpunit_test'),
            'Some test records were not deleted from table "tx_phpunit_test"'
        );

        // Checks whether the second dummy record still exists.
        self::assertSame(
            1,
            $this->subject->countRecords('tx_phpunit_test_article_mm')
        );

        // Runs a deep clean up to delete all dummy records.
        $this->subject->cleanUp(true);
    }

    /**
     * @test
     */
    public function cleanUpWithDeepCleanup()
    {
        // Creates a dummy record (and marks that table as dirty).
        $this->subject->createRecord('tx_phpunit_test');

        // Creates a dummy record directly in the database without putting this
        // table name to the list of dirty tables.
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test_article_mm',
            ['is_dummy_record' => 1]
        );

        // Deletes all dummy records.
        $this->subject->cleanUp(true);

        // Checks whether ALL dummy records were deleted (independent of the
        // list of dirty tables).
        $allowedTables = $this->subject->getListOfDirtyTables();
        foreach ($allowedTables as $currentTable) {
            self::assertSame(
                0,
                $this->subject->countRecords($currentTable),
                'Some test records were not deleted from table "' . $currentTable . '"'
            );
        }
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyFile()
    {
        $fileName = $this->subject->createDummyFile();

        $this->subject->cleanUp();

        self::assertFalse(file_exists($fileName));
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyFolder()
    {
        $folderName = $this->subject->createDummyFolder('test_folder');

        $this->subject->cleanUp();

        self::assertFalse(file_exists($folderName));
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedNestedDummyFolders()
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        $this->subject->cleanUp();

        self::assertFalse(
            file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
        );
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $this->subject->createDummyFile();

        self::assertTrue(is_dir($this->subject->getUploadFolderPath()));

        $this->subject->cleanUp();

        self::assertFalse(is_dir($this->subject->getUploadFolderPath()));
    }

    /**
     * @test
     */
    public function cleanUpExecutesCleanUpHook()
    {
        $this->subject->purgeHooks();

        $cleanUpHookMock = $this->getMock(\Tx_Phpunit_Interface_FrameworkCleanupHook::class, ['cleanUp']);
        $cleanUpHookMock->expects(self::atLeastOnce())->method('cleanUp');

        $hookClassName = get_class($cleanUpHookMock);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp']['phpunit_tests'] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $cleanUpHookMock);

        $this->subject->cleanUp();
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function cleanUpForHookWithoutHookInterfaceThrowsException()
    {
        $this->subject->purgeHooks();

        $hookClassName = uniqid('cleanUpHook');
        $cleanUpHookMock = $this->getMock($hookClassName, ['cleanUp']);

        $GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $cleanUpHookMock;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp']['phpunit_tests'] = $hookClassName;

        $this->subject->cleanUp();
    }

    // ---------------------------------------------------------------------
    // Tests regarding createListOfAllowedTables()
    // The method is called in the constructor of the subject.
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createListOfAllowedTablesContainsOurTestTable()
    {
        $allowedTables = $this->subject->getListOfOwnAllowedTableNames();
        self::assertContains(
            'tx_phpunit_test',
            $allowedTables
        );
    }

    /**
     * @test
     */
    public function createListOfAllowedTablesDoesNotContainForeignTables()
    {
        $allowedTables = $this->subject->getListOfOwnAllowedTableNames();
        self::assertNotContains(
            'be_users',
            $allowedTables
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding createListOfAdditionalAllowedTables()
    // (That method is called in the constructor of the subject.)
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createListOfAdditionalAllowedTablesContainsOurTestTable()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $allowedTables = $this->subject->getListOfAdditionalAllowedTableNames();
        self::assertContains(
            'user_phpunittest_test',
            $allowedTables
        );
    }

    /**
     * @test
     */
    public function createListOfAdditionalAllowedTablesDoesNotContainForeignTables()
    {
        $allowedTables = $this->subject->getListOfAdditionalAllowedTableNames();
        self::assertNotContains(
            'be_users',
            $allowedTables
        );
    }

    /**
     * @test
     */
    public function createListOfAdditionalAllowedTablesContainsOurTestTables()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();
        $this->checkIfExtensionUserPhpUnittest2IsLoaded();

        $subject = new \Tx_Phpunit_Framework(
            'tx_phpunit',
            ['user_phpunittest', 'user_phpunittest2']
        );

        $allowedTables = $subject->getListOfAdditionalAllowedTableNames();
        self::assertContains(
            'user_phpunittest_test',
            $allowedTables
        );
        self::assertContains(
            'user_phpunittest2_test',
            $allowedTables
        );
    }

    /*
     * Tests regarding getAutoIncrement()
     */

    /**
     * @test
     *
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function getAutoIncrementReturnsOneForTruncatedTable()
    {
        \Tx_Phpunit_Service_Database::enableQueryLogging();
        $dbResult = \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_query('TRUNCATE TABLE tx_phpunit_test;');
        if ($dbResult === false) {
            throw new \Tx_Phpunit_Exception_Database(1334438839);
        }

        self::assertSame(
            1,
            $this->subject->getAutoIncrement('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function getAutoIncrementGetsCurrentAutoIncrement()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');

        // $uid will equals be the previous auto increment value, so $uid + 1
        // should be equal to the current auto increment value.
        self::assertSame(
            $uid + 1,
            $this->subject->getAutoIncrement('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function getAutoIncrementForFeUsersTableIsAllowed()
    {
        $this->subject->getAutoIncrement('fe_users');
    }

    /**
     * @test
     */
    public function getAutoIncrementForPagesTableIsAllowed()
    {
        $this->subject->getAutoIncrement('pages');
    }

    /**
     * @test
     */
    public function getAutoIncrementForTtContentTableIsAllowed()
    {
        $this->subject->getAutoIncrement('tt_content');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementWithOtherSystemTableFails()
    {
        $this->subject->getAutoIncrement('sys_domains');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysFileIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysFileCollectionIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file_collection');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysFileReferenceIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file_reference');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysCategoryIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_category');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementForSysCategoryRecordMmFails()
    {
        $this->subject->getAutoIncrement('sys_category_record_mm');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementWithEmptyTableNameFails()
    {
        $this->subject->getAutoIncrement('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementWithForeignTableFails()
    {
        $this->subject->getAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementWithInexistentTableFails()
    {
        $this->subject->getAutoIncrement('tx_phpunit_DOESNOTEXIST');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getAutoIncrementWithTableWithoutUidFails()
    {
        $this->subject->getAutoIncrement('tx_phpunit_test_article_mm');
    }

    // ---------------------------------------------------------------------
    // Tests regarding countRecords()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function countRecordsWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->countRecords('tx_phpunit_test', '');
    }

    /**
     * @test
     */
    public function countRecordsWithMissingWhereClauseIsAllowed()
    {
        $this->subject->countRecords('tx_phpunit_test');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function countRecordsWithEmptyTableNameThrowsException()
    {
        $this->subject->countRecords('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function countRecordsWithInvalidTableNameThrowsException()
    {
        $table = 'foo_bar';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     */
    public function countRecordsWithFeGroupsTableIsAllowed()
    {
        $table = 'fe_groups';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     */
    public function countRecordsWithFeUsersTableIsAllowed()
    {
        $table = 'fe_users';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     */
    public function countRecordsWithPagesTableIsAllowed()
    {
        $table = 'pages';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     */
    public function countRecordsWithTtContentTableIsAllowed()
    {
        $table = 'tt_content';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     */
    public function countRecordsWithSysFileTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file');
    }

    /**
     * @test
     */
    public function countRecordsWithSysFileCollectionTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file_collection');
    }

    /**
     * @test
     */
    public function countRecordsWithSysFileReferenceTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file_reference');
    }

    /**
     * @test
     */
    public function countRecordsWithSysCategoryTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_category');
    }

    /**
     * @test
     */
    public function countRecordsWithSysCategoryRecordMmTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_category_record_mm');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function countRecordsWithOtherTableThrowsException()
    {
        $this->subject->countRecords('sys_domain');
    }

    /**
     * @test
     */
    public function countRecordsReturnsZeroForNoMatches()
    {
        self::assertSame(
            0,
            $this->subject->countRecords('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function countRecordsReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function countRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function countRecordsReturnsTwoForTwoMatches()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertSame(
            2,
            $this->subject->countRecords('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function countRecordsForPagesTableIsAllowed()
    {
        $this->subject->countRecords('pages');
    }

    /**
     * @test
     */
    public function countRecordsIgnoresNonDummyRecords()
    {
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->countRecords(
            'tx_phpunit_test',
            'title = "foo"'
        );

        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        self::assertSame(
            0,
            $testResult
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding existsRecord()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function existsRecordWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->existsRecord('tx_phpunit_test', '');
    }

    /**
     * @test
     */
    public function existsRecordWithMissingWhereClauseIsAllowed()
    {
        $this->subject->existsRecord('tx_phpunit_test');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithEmptyTableNameThrowsException()
    {
        $this->subject->existsRecord('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithInvalidTableNameThrowsException()
    {
        $table = 'foo_bar';
        $this->subject->existsRecord($table);
    }

    /**
     * @test
     */
    public function existsRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordForOneMatchReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordIgnoresNonDummyRecords()
    {
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsRecord(
            'tx_phpunit_test',
            'title = "foo"'
        );

        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        self::assertFalse(
            $testResult
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding existsRecordWithUid()
    // ---------------------------------------------------------------------

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithZeroUidThrowsException()
    {
        $this->subject->existsRecordWithUid('tx_phpunit_test', 0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithNegativeUidThrowsException()
    {
        $this->subject->existsRecordWithUid('tx_phpunit_test', -1);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException()
    {
        $this->subject->existsRecordWithUid('', 1);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException()
    {
        $table = 'foo_bar';
        $this->subject->existsRecordWithUid($table, 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatcheReturnsFalse()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->deleteRecord('tx_phpunit_test', $uid);

        self::assertFalse(
            $this->subject->existsRecordWithUid(
                'tx_phpunit_test',
                $uid
            )
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidForMatchReturnsTrue()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');

        self::assertTrue(
            $this->subject->existsRecordWithUid('tx_phpunit_test', $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidIgnoresNonDummyRecords()
    {
        $uid = \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsRecordWithUid(
            'tx_phpunit_test',
            $uid
        );

        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'uid = ' . $uid
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        self::assertFalse(
            $testResult
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding existsExactlyOneRecord()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->existsExactlyOneRecord('tx_phpunit_test', '');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed()
    {
        $this->subject->existsExactlyOneRecord('tx_phpunit_test');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException()
    {
        $this->subject->existsExactlyOneRecord('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException()
    {
        $table = 'foo_bar';
        $this->subject->existsExactlyOneRecord($table);
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsExactlyOneRecord(
                'tx_phpunit_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForOneMatchReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsExactlyOneRecord(
                'tx_phpunit_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForTwoMatchesReturnsFalse()
    {
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        self::assertFalse(
            $this->subject->existsExactlyOneRecord('tx_phpunit_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordIgnoresNonDummyRecords()
    {
        \Tx_Phpunit_Service_Database::insert(
            'tx_phpunit_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsExactlyOneRecord(
            'tx_phpunit_test',
            'title = "foo"'
        );

        \Tx_Phpunit_Service_Database::delete(
            'tx_phpunit_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        self::assertFalse(
            $testResult
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding resetAutoIncrement()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function resetAutoIncrementForTestTableSucceeds()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        $latestUid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->deleteRecord('tx_phpunit_test', $latestUid);
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        self::assertSame(
            $latestUid,
            $this->subject->getAutoIncrement('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function resetAutoIncrementForUnchangedTestTableCanBeRun()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        // Creates and deletes a record and then resets the auto increment.
        $latestUid = $this->subject->createRecord('user_phpunittest_test');
        $this->subject->deleteRecord('user_phpunittest_test', $latestUid);
        $this->subject->resetAutoIncrement('user_phpunittest_test');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForTableWithoutUidIsAllowed()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_test_article_mm');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForFeUsersTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('fe_users');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForPagesTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('pages');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForTtContentTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('tt_content');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementWithOtherSystemTableFails()
    {
        $this->subject->resetAutoIncrement('sys_domains');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForSysFileTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file');
    }

    /**
     * @test
     *
     */
    public function resetAutoIncrementForSysFileCollectionTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file_collection');
    }

    /**
     * @test
     *
     */
    public function resetAutoIncrementForSysFileReferenceTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file_reference');
    }

    /**
     * @test
     */
    public function resetAutoIncrementForSysCategoryTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_category');
    }

    /**
     * @test
     *
     */
    public function resetAutoIncrementForSysCategoryRecordMmTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_category_record_mm');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementWithEmptyTableNameFails()
    {
        $this->subject->resetAutoIncrement('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementWithForeignTableFails()
    {
        $this->subject->resetAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementWithInexistentTableFails()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_DOESNOTEXIST');
    }

    // ---------------------------------------------------------------------
    // Tests regarding resetAutoIncrementLazily() and
    // setResetAutoIncrementThreshold
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForTestTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('tx_phpunit_test');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForTableWithoutUidIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('tx_phpunit_test_article_mm');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForFeUsersTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('fe_users');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForPagesTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('pages');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForTtContentTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('tt_content');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithOtherSystemTableFails()
    {
        $this->subject->resetAutoIncrementLazily('sys_domains');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForSysFileTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('sys_file');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForSysFileCollectionTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('sys_file_collection');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForSysFileReferenceTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('sys_file_reference');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForSysCategoryTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('sys_category');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyForSysCategoryRecordMmTableIsAllowed()
    {
        $this->subject->resetAutoIncrementLazily('sys_category_record_mm');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithEmptyTableNameFails()
    {
        $this->subject->resetAutoIncrementLazily('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithForeignTableFails()
    {
        $this->subject->resetAutoIncrementLazily('tx_seminars_seminars');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithInexistentTableFails()
    {
        $this->subject->resetAutoIncrementLazily('tx_phpunit_DOESNOTEXIST');
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyDoesNothingAfterOneNewRecordByDefault()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        $oldAutoIncrement = $this->subject->getAutoIncrement('tx_phpunit_test');

        $latestUid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->deleteRecord('tx_phpunit_test', $latestUid);
        $this->subject->resetAutoIncrementLazily('tx_phpunit_test');

        self::assertNotEquals(
            $oldAutoIncrement,
            $this->subject->getAutoIncrement('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function resetAutoIncrementLazilyCleansUpsAfterOneNewRecordWithThresholdOfOne()
    {
        $this->subject->resetAutoIncrement('tx_phpunit_test');

        $oldAutoIncrement = $this->subject->getAutoIncrement('tx_phpunit_test');
        $this->subject->setResetAutoIncrementThreshold(1);

        $latestUid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->deleteRecord('tx_phpunit_test', $latestUid);
        $this->subject->resetAutoIncrementLazily('tx_phpunit_test');

        self::assertSame(
            $oldAutoIncrement,
            $this->subject->getAutoIncrement('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdForOneIsAllowed()
    {
        $this->subject->setResetAutoIncrementThreshold(1);
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdFor100IsAllowed()
    {
        $this->subject->setResetAutoIncrementThreshold(100);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function setResetAutoIncrementThresholdForZeroFails()
    {
        $this->subject->setResetAutoIncrementThreshold(0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function setResetAutoIncrementThresholdForMinus1Fails()
    {
        $this->subject->setResetAutoIncrementThreshold(-1);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createFrontEndPage()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function frontEndPageCanBeCreated()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function createFrontEndPageSetsPageDocumentType()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'doktype',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            1,
            (int)$row['doktype']
        );
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCreatedOnRootPage()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function frontEndPageCanBeCreatedOnOtherPage()
    {
        $parent = $this->subject->createFrontEndPage();
        $uid = $this->subject->createFrontEndPage($parent);

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            $parent,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function frontEndPageCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $uid = $this->subject->createFrontEndPage();
        self::assertNotEquals(
            0,
            $uid
        );

        self::assertNotEquals(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndPage();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /**
     * @test
     */
    public function frontEndPageCanHaveTitle()
    {
        $uid = $this->subject->createFrontEndPage(
            0,
            ['title' => 'Test title']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test title',
            $row['title']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroPid()
    {
        $this->subject->createFrontEndPage(0, ['pid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroPid()
    {
        $this->subject->createFrontEndPage(0, ['pid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroUid()
    {
        $this->subject->createFrontEndPage(0, ['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroUid()
    {
        $this->subject->createFrontEndPage(0, ['uid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroDoktype()
    {
        $this->subject->createFrontEndPage(0, ['doktype' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroDoktype()
    {
        $this->subject->createFrontEndPage(0, ['doktype' => 99999]);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createSystemFolder()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function systemFolderCanBeCreated()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function createSystemFolderSetsSystemFolderDocumentType()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'doktype',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            254,
            (int)$row['doktype']
        );
    }

    /**
     * @test
     */
    public function systemFolderWillBeCreatedOnRootPage()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function systemFolderCanBeCreatedOnOtherPage()
    {
        $parent = $this->subject->createSystemFolder();
        $uid = $this->subject->createSystemFolder($parent);

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            $parent,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function systemFolderCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $uid = $this->subject->createSystemFolder();
        self::assertNotEquals(
            0,
            $uid
        );

        self::assertNotEquals(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function systemFolderWillBeCleanedUp()
    {
        $uid = $this->subject->createSystemFolder();
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function systemFolderHasNoTitleByDefault()
    {
        $uid = $this->subject->createSystemFolder();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /**
     * @test
     */
    public function systemFolderCanHaveTitle()
    {
        $uid = $this->subject->createSystemFolder(
            0,
            ['title' => 'Test title']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test title',
            $row['title']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroPid()
    {
        $this->subject->createSystemFolder(0, ['pid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroPid()
    {
        $this->subject->createSystemFolder(0, ['pid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroUid()
    {
        $this->subject->createSystemFolder(0, ['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroUid()
    {
        $this->subject->createSystemFolder(0, ['uid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroDoktype()
    {
        $this->subject->createSystemFolder(0, ['doktype' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroDoktype()
    {
        $this->subject->createSystemFolder(0, ['doktype' => 99999]);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createContentElement()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function contentElementCanBeCreated()
    {
        $uid = $this->subject->createContentElement();

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'tt_content',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function contentElementWillBeCreatedOnRootPage()
    {
        $uid = $this->subject->createContentElement();

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function contentElementCanBeCreatedOnNonRootPage()
    {
        $parent = $this->subject->createSystemFolder();
        $uid = $this->subject->createContentElement($parent);

        self::assertNotEquals(
            0,
            $uid
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'pid',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            $parent,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function contentElementCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $uid = $this->subject->createContentElement();
        self::assertNotEquals(
            0,
            $uid
        );

        self::assertNotEquals(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function contentElementWillBeCleanedUp()
    {
        $uid = $this->subject->createContentElement();
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'tt_content',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function contentElementHasNoHeaderByDefault()
    {
        $uid = $this->subject->createContentElement();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'header',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['header']
        );
    }

    /**
     * @test
     */
    public function contentElementCanHaveHeader()
    {
        $uid = $this->subject->createContentElement(
            0,
            ['header' => 'Test header']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'header',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test header',
            $row['header']
        );
    }

    /**
     * @test
     */
    public function contentElementIsTextElementByDefault()
    {
        $uid = $this->subject->createContentElement();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'CType',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            'text',
            $row['CType']
        );
    }

    /**
     * @test
     */
    public function contentElementCanHaveOtherType()
    {
        $uid = $this->subject->createContentElement(
            0,
            ['CType' => 'list']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'CType',
            'tt_content',
            'uid = ' . $uid
        );

        self::assertSame(
            'list',
            $row['CType']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function contentElementMustHaveNoZeroPid()
    {
        $this->subject->createContentElement(0, ['pid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function contentElementMustHaveNoNonZeroPid()
    {
        $this->subject->createContentElement(0, ['pid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function contentElementMustHaveNoZeroUid()
    {
        $this->subject->createContentElement(0, ['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function contentElementMustHaveNoNonZeroUid()
    {
        $this->subject->createContentElement(0, ['uid' => 99999]);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createTemplate()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'sys_template',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateCannotBeCreatedOnRootPage()
    {
        $this->subject->createTemplate(0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateCannotBeCreatedWithNegativePageNumber()
    {
        $this->subject->createTemplate(-1);
    }

    /**
     * @test
     */
    public function templateCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );

        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        self::assertNotEquals(
            0,
            $uid
        );

        self::assertNotEquals(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function templateWillBeCleanedUp()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'sys_template',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConfig()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'config',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertFalse(
            isset($row['config'])
        );
    }

    /**
     * @test
     */
    public function templateCanHaveConfig()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['config' => 'plugin.tx_phpunit.test = 1']
        );
        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'config',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertSame(
            'plugin.tx_phpunit.test = 1',
            $row['config']
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConstants()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'constants',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertFalse(
            isset($row['constants'])
        );
    }

    /**
     * @test
     */
    public function templateCanHaveConstants()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['constants' => 'plugin.tx_phpunit.test = 1']
        );
        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'constants',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertSame(
            'plugin.tx_phpunit.test = 1',
            $row['constants']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateMustNotHaveZeroPid()
    {
        $this->subject->createTemplate(42, ['pid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateMustNotHaveNonZeroPid()
    {
        $this->subject->createTemplate(42, ['pid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateMustHaveNoZeroUid()
    {
        $this->subject->createTemplate(42, ['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function templateMustNotHaveNonZeroUid()
    {
        $this->subject->createTemplate(42, ['uid' => 99999]);
    }

    /*
     * Tests regarding createDummyFile()
     */

    /**
     * @test
     */
    public function createDummyFileCreatesFile()
    {
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    /**
     * @test
     */
    public function createDummyFileCreatesFileInSubFolder()
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');
        $dummyFile = $this->subject->createDummyFile(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder) . '/test.txt'
        );

        self::assertFileExists($dummyFile);
    }

    /**
     * @test
     */
    public function createDummyFileCreatesFileWithTheProvidedContent()
    {
        $dummyFile = $this->subject->createDummyFile('test.txt', 'Hello world!');

        self::assertSame('Hello world!', file_get_contents($dummyFile));
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $this->subject->createDummyFile();

        self::assertTrue(is_dir($this->subject->getUploadFolderPath()));
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createDummyZipArchive()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createDummyZipArchiveForNoContentProvidedCreatesZipArchive()
    {
        $this->markAsSkippedForNoZipArchive();

        $dummyFile = $this->subject->createDummyZipArchive();

        self::assertTrue(file_exists($dummyFile));
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForFileNameInSubFolderProvidedCreatesZipArchiveInSubFolder()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFolder = $this->subject->getPathRelativeToUploadDirectory(
            $this->subject->createDummyFolder('sub-folder')
        );
        $this->subject->createDummyZipArchive($dummyFolder . 'foo.zip');

        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . $dummyFolder . 'foo.zip')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForNoContentProvidedCreatesZipArchiveWithDummyFile()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyZipArchive();
        $zip = new \ZipArchive();
        $zip->open($dummyFile);
        $zip->extractTo($this->subject->getUploadFolderPath());
        $zip->close();

        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . 'test.txt')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForFileProvidedCreatesZipArchiveWithThatFile()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyZipArchive(
            'foo.zip',
            [$this->subject->createDummyFile('bar.txt')]
        );
        $zip = new \ZipArchive();
        $zip->open($dummyFile);
        $zip->extractTo($this->subject->getUploadFolderPath());
        $zip->close();

        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . 'bar.txt')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForFileProvidedWithContentCreatesZipArchiveWithThatFileAndContentInIt()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyZipArchive(
            'foo.zip',
            [$this->subject->createDummyFile(
                'bar.txt',
                'foo bar'
            )]
        );
        $zip = new \ZipArchive();
        $zip->open($dummyFile);
        $zip->extractTo($this->subject->getUploadFolderPath());
        $zip->close();

        self::assertSame(
            'foo bar',
            file_get_contents($this->subject->getUploadFolderPath() . 'bar.txt')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForTwoFilesProvidedCreatesZipArchiveWithTheseFiles()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyZipArchive(
            'foo.zip',
            [
            $this->subject->createDummyFile('foo.txt'),
            $this->subject->createDummyFile('bar.txt'),
            ]
        );
        $zip = new \ZipArchive();
        $zip->open($dummyFile);
        $zip->extractTo($this->subject->getUploadFolderPath());
        $zip->close();

        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . 'foo.txt')
        );
        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . 'bar.txt')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForFileInSubFolderOfUploadFolderProvidedCreatesZipArchiveWithFileInSubFolder()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $this->subject->createDummyFolder('sub-folder');
        $dummyFile = $this->subject->createDummyZipArchive(
            'foo.zip',
            [$this->subject->createDummyFile('sub-folder/foo.txt')]
        );
        $zip = new \ZipArchive();
        $zip->open($dummyFile);
        $zip->extractTo($this->subject->getUploadFolderPath());
        $zip->close();

        self::assertTrue(
            file_exists($this->subject->getUploadFolderPath() . 'sub-folder/foo.txt')
        );
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesUploadFolder()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $this->subject->createDummyZipArchive();

        self::assertTrue(is_dir($this->subject->getUploadFolderPath()));
    }

    /**
     * @test
     */
    public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        $this->markAsSkippedForNoZipArchive();

        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFile = $this->subject->createDummyZipArchive();

        self::assertTrue(file_exists($dummyFile));
    }

    // ---------------------------------------------------------------------
    // Tests regarding deleteDummyFile()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function deleteDummyFileDeletesCreatedDummyFile()
    {
        $dummyFile = $this->subject->createDummyFile();
        $this->subject->deleteDummyFile(basename($dummyFile));

        self::assertFalse(file_exists($dummyFile));
    }

    /**
     * @test
     */
    public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException()
    {
        $dummyFile = $this->subject->createDummyFile();
        unlink($dummyFile);

        $this->subject->deleteDummyFile(basename($dummyFile));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteDummyFileWithInexistentFileThrowsException()
    {
        $uniqueFileName = $this->subject->getUniqueFileOrFolderPath('test.txt');

        $this->subject->deleteDummyFile(basename($uniqueFileName));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteDummyFileWithForeignFileThrowsException()
    {
        vfsStream::setup('root/');
        $testFileUrl = vfsStream::url('root/test.txt');

        $this->subject->deleteDummyFile($testFileUrl);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createDummyFolder()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createDummyFolderCreatesFolder()
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertTrue(is_dir($dummyFolder));
    }

    /**
     * @test
     */
    public function createDummyFolderCanCreateFolderInDummyFolder()
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        self::assertTrue(is_dir($innerDummyFolder));
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $this->subject->createDummyFolder('test_folder');

        self::assertTrue(is_dir($this->subject->getUploadFolderPath()));
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertTrue(is_dir($dummyFolder));
    }

    // ---------------------------------------------------------------------
    // Tests regarding deleteDummyFolder()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function deleteDummyFolderDeletesCreatedDummyFolder()
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');
        $this->subject->deleteDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder)
        );

        self::assertFalse(is_dir($dummyFolder));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteDummyFolderWithInexistentFolderThrowsException()
    {
        $uniqueFolderName = $this->subject->getUniqueFileOrFolderPath('test_folder');

        $this->subject->deleteDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($uniqueFolderName)
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function deleteDummyFolderWithForeignFolderThrowsException()
    {
        $uniqueFolderName = $this->subject->getUniqueFileOrFolderPath('test_folder');
        GeneralUtility::mkdir($uniqueFolderName);
        $this->foreignFolderToDelete = $uniqueFolderName;

        $this->subject->deleteDummyFolder(basename($uniqueFolderName));
    }

    /**
     * @test
     */
    public function deleteDummyFolderCanDeleteCreatedDummyFolderInDummyFolder()
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        $this->subject->deleteDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($innerDummyFolder)
        );

        self::assertFalse(file_exists($innerDummyFolder));
        self::assertTrue(file_exists($outerDummyFolder));
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function deleteDummyFolderWithNonEmptyDummyFolderThrowsException()
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');
        $this->subject->createDummyFile(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder) .
            '/test.txt'
        );

        $this->subject->deleteDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder)
        );
    }

    /**
     * @test
     */
    public function deleteDummyFolderWithFolderNameConsistingOnlyOfNumbersDoesNotThrowAnException()
    {
        $dummyFolder = $this->subject->createDummyFolder('123');

        $this->subject->deleteDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder)
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding set- and getUploadFolderPath()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix()
    {
        self::assertRegExp(
            '/\/uploads\/tx_phpunit\/$/',
            $this->subject->getUploadFolderPath()
        );
    }

    /**
     * @test
     */
    public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath()
    {
        $this->subject->setUploadFolderPath('/foo/bar/');

        self::assertSame(
            '/foo/bar/',
            $this->subject->getUploadFolderPath()
        );
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function setUploadFolderPathAfterCreatingDummyFileThrowsException()
    {
        $this->subject->createDummyFile();
        $this->subject->setUploadFolderPath('/foo/bar/');
    }

    // ---------------------------------------------------------------------
    // Tests regarding getPathRelativeToUploadDirectory()
    // ---------------------------------------------------------------------

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException()
    {
        $this->subject->getPathRelativeToUploadDirectory(PATH_site);
    }

    /*
     * Tests regarding getUniqueFileOrFolderPath()
     */

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathWithEmptyPathThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The first parameter $path must not be empty.'
        );

        $this->subject->getUniqueFileOrFolderPath('');
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathReturnsNonEmptyString()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo.txt');

        self::assertNotSame('', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForPathWithoutExtensionReturnsPathWithoutExtension()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo');

        self::assertRegExp('/\/foo(\\-\\d+})?$/', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForPathWithExtensionReturnsPathWithSameExtension()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo.txt');

        self::assertRegExp('/.+\\.txt$/', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathReturnsPathWithSameBaseName()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo.txt');

        self::assertRegExp('/foo.*\\.txt$/', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForPathWithFolderReturnsPathWithSameFolder()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo/bar.txt');

        self::assertRegExp('/foo\\/bar.*\.txt$/', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForPathWithoutExtensionReturnsNoExtension()
    {
        $result = $this->subject->getUniqueFileOrFolderPath('foo');

        self::assertNotRegExp('/\\.[a-z]+$/', $result);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForFileWithExtensionReturnsPathWithSingleSeparators()
    {
        $path = $this->subject->getUniqueFileOrFolderPath('test.txt');

        self::assertNotContains('//', $path);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathForFileWithoutExtensionReturnsPathWithSingleSeparators()
    {
        $path = $this->subject->getUniqueFileOrFolderPath('test');

        self::assertNotContains('//', $path);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathReturnsPathToNonexistentFile()
    {
        $path = $this->subject->getUniqueFileOrFolderPath('foo.txt');

        self::assertFileNotExists($path);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathWithPathOfInexistentFileReturnsThatFile()
    {
        $path = $this->subject->createDummyFile();
        $baseName = basename($path);
        $this->subject->deleteDummyFile($baseName);

        $uniquePath = $this->subject->getUniqueFileOrFolderPath($baseName);

        self::assertSame($path, $uniquePath);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathWithPathOfExistingFileReturnsNewPathWithSuffix()
    {
        $path = $this->subject->createDummyFile('test.txt');
        $baseName = basename($path);

        $uniquePath = $this->subject->getUniqueFileOrFolderPath($baseName);

        self::assertRegExp('/\\-\\d+\\.txt/', $uniquePath);
    }

    /**
     * @test
     */
    public function getUniqueFileOrFolderPathWithPathOfExistingFileReturnsNewPathOfInexistentFile()
    {
        $path = $this->subject->createDummyFile();
        $baseName = basename($path);

        $uniquePath = $this->subject->getUniqueFileOrFolderPath($baseName);

        self::assertFileNotExists($uniquePath);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createFrontEndUserGroup()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function frontEndUserGroupCanBeCreated()
    {
        $uid = $this->subject->createFrontEndUserGroup();

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_groups',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupTableCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $uid = $this->subject->createFrontEndUserGroup();
        self::assertNotEquals(
            0,
            $uid
        );

        self::assertNotEquals(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupTableWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndUserGroup();
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'fe_groups',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndUserGroup();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'fe_groups',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupCanHaveTitle()
    {
        $uid = $this->subject->createFrontEndUserGroup(
            ['title' => 'Test title']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'title',
            'fe_groups',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test title',
            $row['title']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserGroupMustHaveNoZeroUid()
    {
        $this->subject->createFrontEndUserGroup(['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserGroupMustHaveNoNonZeroUid()
    {
        $this->subject->createFrontEndUserGroup(['uid' => 99999]);
    }

    // ---------------------------------------------------------------------
    // Tests regarding createFrontEndUser()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function frontEndUserCanBeCreated()
    {
        $uid = $this->subject->createFrontEndUser();

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserTableCanBeDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $uid = $this->subject->createFrontEndUser();
        self::assertNotEquals(
            0,
            $uid
        );

        self::greaterThan(
            1,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function frontEndUserTableWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndUser();
        self::assertNotEquals(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault()
    {
        $uid = $this->subject->createFrontEndUser();

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'username',
            'fe_users',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveUserName()
    {
        $uid = $this->subject->createFrontEndUser(
            '',
            ['username' => 'Test name']
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'username',
            'fe_users',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test name',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveSeveralUserGroups()
    {
        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();
        $uid = $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', ' . $feUserGroupUidThree
        );

        self::assertNotEquals(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUid()
    {
        $this->subject->createFrontEndUser('', ['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoNonZeroUid()
    {
        $this->subject->createFrontEndUser('', ['uid' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray()
    {
        $this->subject->createFrontEndUser('', ['usergroup' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray()
    {
        $this->subject->createFrontEndUser('', ['usergroup' => 99999]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray()
    {
        $this->subject->createFrontEndUser(
            '',
            ['usergroup' => '1,2,4,5']
        );
    }

    /**
     * @test
     */
    public function createFrontEndUserWithEmptyGroupCreatesGroup()
    {
        $this->subject->createFrontEndUser('');

        self::assertTrue(
            $this->subject->existsExactlyOneRecord('fe_groups')
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided()
    {
        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', 0, ' . $feUserGroupUidThree
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList()
    {
        $feUserGroupUid = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUid . ', abc'
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding createBackEndUser()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createBackEndUserReturnsUidGreaterZero()
    {
        self::assertNotEquals(
            0,
            $this->subject->createBackEndUser()
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesBackEndUserRecordInTheDatabase()
    {
        self::assertSame(
            1,
            $this->subject->countRecords(
                'be_users',
                'uid=' . $this->subject->createBackEndUser()
            )
        );
    }

    /**
     * @test
     */
    public function createBackEndUserMarksBackEndUserTableAsDirty()
    {
        self::assertSame(
            0,
            count($this->subject->getListOfDirtySystemTables())
        );
        $this->subject->createBackEndUser();

        self::greaterThan(
            1,
            count($this->subject->getListOfDirtySystemTables())
        );
    }

    /**
     * @test
     */
    public function cleanUpCleansUpDirtyBackEndUserTable()
    {
        $uid = $this->subject->createBackEndUser();

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('be_users', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault()
    {
        $uid = $this->subject->createBackEndUser();

        $row = \Tx_Phpunit_Service_Database::selectSingle('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            '',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName()
    {
        $uid = $this->subject->createBackEndUser(['username' => 'Test name']);

        $row = \Tx_Phpunit_Service_Database::selectSingle('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            'Test name',
            $row['username']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsExeption()
    {
        $this->subject->createBackEndUser(['uid' => 0]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption()
    {
        $this->subject->createBackEndUser(['uid' => 999999]);
    }

    // ---------------------------------------------------------------------
    // Tests concerning fakeFrontend
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function createFakeFrontEndCreatesGlobalFrontEnd()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TSFE']
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsPositivePageUidIfCalledWithoutParameters()
    {
        $this->subject->createFrontEndPage();
        self::assertGreaterThan(
            0,
            $this->subject->createFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsCurrentFrontEndPageUid()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $result = $this->subject->createFakeFrontEnd();

        self::assertSame(
            $GLOBALS['TSFE']->id,
            $result
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesNullTimeTrackInstance()
    {
        $GLOBALS['TT'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker',
            $GLOBALS['TT']
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesSysPage()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\\Frontend\\Page\\PageRepository',
            $GLOBALS['TSFE']->sys_page
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesFrontEndUser()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication',
            $GLOBALS['TSFE']->fe_user
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesContentObject()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer',
            $GLOBALS['TSFE']->cObj
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesTemplate()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            'TYPO3\\CMS\Core\\TypoScript\\TemplateService',
            $GLOBALS['TSFE']->tmpl
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReadsTypoScriptSetupFromPage()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageUid,
            ['config' => 'foo = bar']
        );

        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            'bar',
            $GLOBALS['TSFE']->tmpl->setup['foo']
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageUid,
            ['config' => 'foo = 42']
        );

        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            1,
            $GLOBALS['TSFE']->tmpl->loaded
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesConfiguration()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertTrue(
            is_array($GLOBALS['TSFE']->config)
        );
    }

    /**
     * @test
     */
    public function loginUserIsFalseAfterCreateFakeFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertSame(
            false,
            $GLOBALS['TSFE']->loginUser
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsDefaultGroupList()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertSame(
            '0,-1',
            $GLOBALS['TSFE']->gr_list
        );
    }

    /**
     * @test
     */
    public function discardFakeFrontEndNullsOutGlobalFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->discardFakeFrontEnd();

        self::assertNull(
            $GLOBALS['TSFE']
        );
    }

    /**
     * @test
     */
    public function discardFakeFrontEndNullsOutGlobalTimeTrack()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->discardFakeFrontEnd();

        self::assertNull(
            $GLOBALS['TT']
        );
    }

    /**
     * @test
     */
    public function discardFakeFrontEndCanBeCalledTwoTimes()
    {
        $this->subject->discardFakeFrontEnd();
        $this->subject->discardFakeFrontEnd();
    }

    /**
     * @test
     */
    public function hasFakeFrontEndInitiallyIsFalse()
    {
        self::assertFalse(
            $this->subject->hasFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function hasFakeFrontEndIsTrueAfterCreateFakeFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertTrue(
            $this->subject->hasFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function hasFakeFrontEndIsFalseAfterCreateAndDiscardFakeFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->discardFakeFrontEnd();

        self::assertFalse(
            $this->subject->hasFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function cleanUpDiscardsFakeFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->cleanUp();

        self::assertFalse(
            $this->subject->hasFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsProvidedPageUid()
    {
        $pageUid = $this->subject->createFrontEndPage();

        self::assertSame(
            $pageUid,
            $this->subject->createFakeFrontEnd($pageUid)
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            $pageUid,
            $GLOBALS['TSFE']->id
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function createFakeFrontThrowsExceptionForNegativePageUid()
    {
        $this->subject->createFakeFrontEnd(-1);
    }

    // ---------------------------------------------------------------------
    // Tests regarding user login and logout
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function isLoggedInInitiallyIsFalse()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function isLoggedThrowsExceptionWithoutFrontEnd()
    {
        $this->subject->isLoggedIn();
    }

    /**
     * @test
     */
    public function loginFrontEndUserSwitchesToLoggedIn()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $feUserId = $this->subject->createFrontEndUser();
        $this->subject->loginFrontEndUser($feUserId);

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function loginFrontEndUserSetsLoginUserToOne()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $feUserId = $this->subject->createFrontEndUser();
        $this->subject->loginFrontEndUser($feUserId);

        self::assertSame(
            1,
            $GLOBALS['TSFE']->loginUser
        );
    }

    /**
     * @test
     */
    public function loginFrontEndUserRetrievesNameOfUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $feUserId = $this->subject->createFrontEndUser(
            '',
            ['name' => 'John Doe']
        );
        $this->subject->loginFrontEndUser($feUserId);

        self::assertSame(
            'John Doe',
            $GLOBALS['TSFE']->fe_user->user['name']
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function loginFrontEndUserWithZeroUidThrowsException()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->loginFrontEndUser(0);
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function loginFrontEndUserWithoutFrontEndThrowsException()
    {
        $feUserId = $this->subject->createFrontEndUser();
        $this->subject->loginFrontEndUser($feUserId);
    }

    /**
     * @test
     */
    public function loginFrontEndUserSetsGroupDataOfUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $feUserGroupUid = $this->subject->createFrontEndUserGroup(
            ['title' => 'foo']
        );
        $feUserId = $this->subject->createFrontEndUser($feUserGroupUid);
        $this->subject->loginFrontEndUser($feUserId);

        self::assertSame(
            [$feUserGroupUid => 'foo'],
            $GLOBALS['TSFE']->fe_user->groupData['title']
        );
    }

    /**
     * @test
     */
    public function logoutFrontEndUserAfterLoginSwitchesToNotLoggedIn()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $feUserId = $this->subject->createFrontEndUser();
        $this->subject->loginFrontEndUser($feUserId);
        $this->subject->logoutFrontEndUser();

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToFalse()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->logoutFrontEndUser();

        self::assertSame(
            false,
            $GLOBALS['TSFE']->loginUser
        );
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function logoutFrontEndUserWithoutFrontEndThrowsException()
    {
        $this->subject->logoutFrontEndUser();
    }

    /**
     * @test
     */
    public function logoutFrontEndUserCanBeCalledTwoTimes()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->logoutFrontEndUser();
        $this->subject->logoutFrontEndUser();
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserCreatesFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser();

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser(
            '',
            ['name' => 'John Doe']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users', 'name = "John Doe"')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserLogsInFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $frontEndUserUid = $this->subject->createAndLoginFrontEndUser(
            $frontEndUserGroupUid
        );

        $dbResultRow = \Tx_Phpunit_Service_Database::selectSingle(
            'usergroup',
            'fe_users',
            'uid = ' . $frontEndUserUid
        );

        self::assertSame(
            $frontEndUserGroupUid,
            (int)$dbResultRow['usergroup']
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser(
            $frontEndUserGroupUid
        );

        self::assertSame(
            1,
            $this->subject->countRecords('fe_groups')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    // ---------------------------------------------------------------------
    // Tests regarding increaseRelationCounter()
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function increaseRelationCounterIncreasesNonZeroFieldValueByOne()
    {
        $uid = $this->subject->createRecord(
            'tx_phpunit_test',
            ['related_records' => 41]
        );

        $this->subject->increaseRelationCounter(
            'tx_phpunit_test',
            $uid,
            'related_records'
        );

        $row = \Tx_Phpunit_Service_Database::selectSingle(
            'related_records',
            'tx_phpunit_test',
            'uid = ' . $uid
        );

        self::assertSame(
            42,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_Database
     */
    public function increaseRelationCounterThrowsExceptionOnInvalidUid()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $invalidUid = $uid + 1;

        $this->subject->increaseRelationCounter(
            'tx_phpunit_test',
            $invalidUid,
            'related_records'
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function increaseRelationCounterThrowsExceptionOnInvalidTableName()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');

        $this->subject->increaseRelationCounter(
            'tx_phpunit_inexistent',
            $uid,
            'related_records'
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function increaseRelationCounterThrowsExceptionOnInexistentFieldName()
    {
        $uid = $this->subject->createRecord('tx_phpunit_test');
        $this->subject->increaseRelationCounter(
            'tx_phpunit_test',
            $uid,
            'inexistent_column'
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForExtensionTableReturnsDummyColumnName()
    {
        self::assertSame(
            'is_dummy_record',
            $this->subject->getDummyColumnName('tx_phpunit_test')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForSystemTableReturnsPhpUnitPrefixedColumnName()
    {
        self::assertSame(
            'tx_phpunit_is_dummy_record',
            $this->subject->getDummyColumnName('fe_users')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName()
    {
        $this->checkIfExtensionUserPhpUnittestIsLoaded();

        $testingFramework = new \Tx_Phpunit_Framework(
            'user_phpunittest',
            ['user_phpunittest2']
        );
        self::assertSame(
            'user_phpunittest_is_dummy_record',
            $testingFramework->getDummyColumnName('user_phpunittest2_test')
        );
    }

    /*
     * Tests concerning createBackEndUserGroup
     */

    /**
     * @test
     */
    public function createBackEndUserGroupForNoDataGivenCreatesBackEndGroup()
    {
        $this->subject->createBackEndUserGroup([]);

        self::assertTrue(
            $this->subject->existsRecord('be_groups')
        );
    }

    /**
     * @test
     */
    public function createBackEndUserGroupForNoDataGivenReturnsUidOfCreatedBackEndGroup()
    {
        $backendGroupUid = $this->subject->createBackEndUserGroup([]);

        self::assertTrue(
            $this->subject->existsRecord(
                'be_groups',
                'uid = ' . $backendGroupUid
            )
        );
    }

    /**
     * @test
     */
    public function createBackEndUserGroupForTitleGivenStoresTitleInGroupRecord()
    {
        $this->subject->createBackEndUserGroup(
            ['title' => 'foo group']
        );

        self::assertTrue(
            $this->subject->existsRecord(
                'be_groups',
                'title = "foo group"'
            )
        );
    }
}
