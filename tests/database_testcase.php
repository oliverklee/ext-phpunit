<?php
/**
 * These testcases requires that the following extensions are installed
 *  1. aaa
 *  2. bbb (depends on aaa and alters aaa' tables)
 *  3. ccc (depends on bbb)
 *  4. ddd (depends on bbb)
 */
class database_testcase extends tx_phpunit_database_testcase {
	public function tearDown() {
		// ensures that test database always is dropped
		// even when testcases fails
		$this->dropDatabase();
	}

	/**
	 * @test
	 */
	public function nullToEmptyString() {
		$this->assertEquals('', mysql_real_escape_string(null));
	}

	/**
	 * @test
	 */
	public function creatingTestDatabase() {
		if (!$this->dropDatabase() || !$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}

		$db = $GLOBALS['TYPO3_DB'];

		$databaseNames = $db->admin_get_dbs();

		$this->assertContains($this->testDatabase, $databaseNames);
	}

	/**
	 * @test
	 */
	public function droppingTestDatabase() {
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (!in_array($this->testDatabase, $databaseNames)) {
			if (!$this->createDatabase()) {
				$this->markTestSkipped(
					'This test can only be run if the current DB user has the ' .
						'permissions to CREATE and DROP databases.'
				);
			}
			$databaseNames = $db->admin_get_dbs();
			$this->assertContains($this->testDatabase, $databaseNames);
		}

		if (!$this->dropDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$databaseNames = $db->admin_get_dbs();
		$this->assertNotContains($this->testDatabase, $databaseNames);
	}

	/**
	 * @test
	 */
	public function cleaningDatabase() {
		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->importExtensions(array('tsconfig_help'));

		$db = $this->useTestDatabase();
		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);
		$this->assertNotEquals(0, $rows);

		$this->cleanDatabase();
		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);
		$this->assertEquals(0, $rows);
	}

	/**
	 * @test
	 */
	public function importingExtension() {
		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('tsconfig_help'));

		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);

		$this->assertNotEquals(0, $rows);
	}

	/**
	 * @test
	 */
	public function extensionAlteringTable() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa and bbb ' .
					'from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('bbb'), true);

		$tableNames = $this->getDatabaseTables();
		$this->assertContains('tx_bbb_test', $tableNames, 'Check that extension bbb is installed. The extension can be found in tests/res/.');
		$this->assertContains('tx_aaa_test', $tableNames, 'Check that extension aaa is installed. The extension can be found in tests/res/.');

		// extension BBB extends an AAA table
		$columns = $db->admin_get_fields('tx_aaa_test');
		$this->assertContains('tx_bbb_test', array_keys($columns));
	}

	/**
	 * @test
	 */
	public function recursiveImportingExtensions() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')
			|| !t3lib_extMgm::isLoaded('ccc')
		) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa, bbb and ccc ' .
					'from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->useTestDatabase();
		$this->importExtensions(array('ccc', 'aaa'), true);

		$tableNames = $this->getDatabaseTables();

		$this->assertContains('tx_ccc_test', $tableNames, 'Check that extension ccc is installed. The extension can be found in tests/res/.');
		$this->assertContains('tx_bbb_test', $tableNames, 'Check that extension bbb is installed. The extension can be found in tests/res/.');
		$this->assertContains('tx_aaa_test', $tableNames, 'Check that extension aaa is installed. The extension can be found in tests/res/.');
	}

	/**
	 * @test
	 */
	public function skippingDependencyExtensions() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')
			|| !t3lib_extMgm::isLoaded('ccc') || !t3lib_extMgm::isLoaded('ddd')
		) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa, bbb, ccc ' .
					'and ddd from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->useTestDatabase();

		$toSkip = array('bbb');
		$this->importExtensions(array('ccc', 'ddd'), true, $toSkip);

		$tableNames = $this->getDatabaseTables();

		$this->assertContains('tx_ccc_test', $tableNames, 'Check that extension ccc is installed. The extension can be found in tests/res/.');
		$this->assertContains('tx_ddd_test', $tableNames, 'Check that extension ddd is installed. The extension can be found in tests/res/.');
		$this->assertNotContains('tx_bbb_test', $tableNames);
		$this->assertNotContains('tx_aaa_test', $tableNames);
	}

	/**
	 * @test
	 */
	public function importingDataSet() {
		if (!t3lib_extMgm::isLoaded('ccc')) {
			$this->markTestSkipped(
				'This test can only be run if the extension ccc from ' .
					'tests/res is installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('ccc'));
		$this->importDataSet(dirname(__FILE__). '/database_testcase_dataset.xml');

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_test', null);
		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result[0]['uid']);
		$this->assertEquals(2, $result[1]['uid']);

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_data', null);
		$this->assertEquals(1, count($result));
		$this->assertEquals(1, $result[0]['uid']);

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_data_test_mm', null);
		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result[0]['uid_local']);
		$this->assertEquals(1, $result[0]['uid_foreign']);
		$this->assertEquals(1, $result[1]['uid_local']);
		$this->assertEquals(2, $result[1]['uid_foreign']);
	}
}
?>