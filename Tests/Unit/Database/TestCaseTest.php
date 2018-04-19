<?php
namespace OliverKlee\Phpunit\Tests\Unit\Database;

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

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * These test cases require that the following extensions are installed:
 *  1. aaa
 *  2. bbb (depends on aaa and alters aaa' tables)
 *  3. ccc (depends on bbb)
 *  4. ddd (depends on bbb)
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseTest extends \Tx_Phpunit_Database_TestCase
{
    /**
     * @var string
     */
    const DB_PERMISSIONS_MESSAGE
        = 'Please make sure that the current DB user has global SELECT, INSERT, CREATE, ALTER and DROP permissions.';

    /**
     * @var DatabaseConnection
     */
    protected $db = null;

    protected function setUp()
    {
        $this->createDatabaseAndCheckResult();
        $this->db = $this->useTestDatabase();
    }

    protected function tearDown()
    {
        $this->dropDatabasedAndCheckResult();
        $this->switchToTypo3Database();
    }

    /*
     * Utility functions
     */

    /**
     * Marks the current test as skipped, mentioning the necessary DB privileges.
     *
     * @return void
     */
    protected function markTestAsSkipped()
    {
        self::markTestSkipped(self::DB_PERMISSIONS_MESSAGE);
    }

    /**
     * Creates the test database and checks the result.
     *
     * If the test database cannot be created, the current test will be marked as skipped.
     *
     * @return void
     */
    protected function createDatabaseAndCheckResult()
    {
        if (!$this->createDatabase()) {
            $this->markTestAsSkipped();
        }
    }

    /**
     * Drops the test database and checks the result.
     *
     * If the test database cannot be dropped, the current test will be marked as skipped.
     *
     * @return void
     */
    protected function dropDatabasedAndCheckResult()
    {
        if (!$this->dropDatabase()) {
            $this->markTestAsSkipped();
        }
    }

    /**
     * @test
     */
    public function cleaningDatabase()
    {
        $this->importExtensions(['extbase']);

        /** @var \mysqli_result|resource $res */
        $res = $this->db->sql_query('show tables');
        $rows = $this->db->sql_num_rows($res);
        self::assertNotEquals(0, $rows);

        // Check DROP privilege as it is needed for clean up
        $this->dropDatabasedAndCheckResult();
        $this->createDatabase();
        $this->cleanDatabase();
        /** @var \mysqli_result|resource $res */
        $res = $this->db->sql_query('show tables');

        self::assertSame(
            0,
            $this->db->sql_num_rows($res)
        );
    }

    /**
     * @test
     */
    public function importingExtension()
    {
        $this->importExtensions(['extbase']);

        /** @var \mysqli_result|resource $res */
        $res = $this->db->sql_query('show tables');
        $rows = $this->db->sql_num_rows($res);

        self::assertNotSame(
            0,
            $rows
        );
    }

    /**
     * @test
     */
    public function extensionAlteringTable()
    {
        if (!ExtensionManagementUtility::isLoaded('aaa') || !ExtensionManagementUtility::isLoaded('bbb')) {
            self::markTestSkipped(
                'This test can only be run if the extensions aaa and bbb ' .
                'from TestExtensions/ are installed.'
            );
        }

        $this->importExtensions(['bbb'], true);

        $tableNames = $this->getDatabaseTables();
        self::assertContains(
            'tx_bbb_test',
            $tableNames,
            'Check that extension bbb is installed. The extension can be found in TestExtensions/.'
        );
        self::assertContains(
            'tx_aaa_test',
            $tableNames,
            'Check that extension aaa is installed. The extension can be found in TestExtensions/.'
        );

        // extension BBB extends an AAA table
        $columns = $this->db->admin_get_fields('tx_aaa_test');
        self::assertContains(
            'tx_bbb_test',
            array_keys($columns),
            self::DB_PERMISSIONS_MESSAGE
        );
    }

    /**
     * @test
     */
    public function recursiveImportingExtensions()
    {
        if (!ExtensionManagementUtility::isLoaded('aaa') || !ExtensionManagementUtility::isLoaded('bbb')
            || !ExtensionManagementUtility::isLoaded('ccc')
        ) {
            self::markTestSkipped(
                'This test can only be run if the extensions aaa, bbb and ccc ' .
                'from TestExtensions/ are installed.'
            );
        }

        $this->importExtensions(['ccc', 'aaa'], true);

        $tableNames = $this->getDatabaseTables();

        self::assertContains(
            'tx_ccc_test',
            $tableNames,
            'Check that extension ccc is installed. The extension can be found in TestExtensions/.'
        );
        self::assertContains(
            'tx_bbb_test',
            $tableNames,
            'Check that extension bbb is installed. The extension can be found in TestExtensions/.'
        );
        self::assertContains(
            'tx_aaa_test',
            $tableNames,
            'Check that extension aaa is installed. The extension can be found in TestExtensions/.'
        );
    }

    /**
     * @test
     */
    public function skippingDependencyExtensions()
    {
        if (!ExtensionManagementUtility::isLoaded('aaa') || !ExtensionManagementUtility::isLoaded('bbb')
            || !ExtensionManagementUtility::isLoaded('ccc') || !ExtensionManagementUtility::isLoaded('ddd')
        ) {
            self::markTestSkipped(
                'This test can only be run if the extensions aaa, bbb, ccc ' .
                'and ddd from TestExtensions/ are installed.'
            );
        }

        $toSkip = ['bbb'];
        $this->importExtensions(['ccc', 'ddd'], true, $toSkip);

        $tableNames = $this->getDatabaseTables();

        self::assertContains(
            'tx_ccc_test',
            $tableNames,
            'Check that extension ccc is installed. The extension can be found in TestExtensions/.'
        );
        self::assertContains(
            'tx_ddd_test',
            $tableNames,
            'Check that extension ddd is installed. The extension can be found in TestExtensions/.'
        );
        self::assertNotContains(
            'tx_bbb_test',
            $tableNames,
            self::DB_PERMISSIONS_MESSAGE
        );
        self::assertNotContains('tx_aaa_test', $tableNames);
    }

    /**
     * @test
     */
    public function importingDataSet()
    {
        if (!ExtensionManagementUtility::isLoaded('ccc')) {
            self::markTestSkipped(
                'This test can only be run if the extension ccc from TestExtensions/ is installed.'
            );
        }

        $this->importExtensions(['ccc']);
        $this->importDataSet(ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Database/Fixtures/DataSet.xml');

        $result = $this->db->exec_SELECTgetRows('*', 'tx_ccc_test', null);
        self::assertSame(
            2,
            count($result),
            self::DB_PERMISSIONS_MESSAGE
        );
        self::assertSame(
            '1',
            $result[0]['uid']
        );
        self::assertSame(
            '2',
            $result[1]['uid']
        );

        $result = $this->db->exec_SELECTgetRows('*', 'tx_ccc_data', null);
        self::assertSame(
            1,
            count($result)
        );
        self::assertSame(
            '1',
            $result[0]['uid']
        );

        $result = $this->db->exec_SELECTgetRows('*', 'tx_ccc_data_test_mm', null);
        self::assertSame(
            2,
            count($result)
        );
        self::assertSame(
            '1',
            $result[0]['uid_local']
        );
        self::assertSame(
            '1',
            $result[0]['uid_foreign']
        );
        self::assertSame(
            '1',
            $result[1]['uid_local']
        );
        self::assertSame(
            '2',
            $result[1]['uid_foreign']
        );
    }
}
