<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2005 Robert Lemke (robert@typo3.org)
*  (c) 2007 Kasper Ligaard (kli@systime.dk)
*  (c) 2008 Soren Soltveit (sso@systime.dk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class provides helper functions, that might be convenient when testing in
 * Typo3. It extends PHPUnit_Framework_TestCase, so you have access to all of that 
 * class too.
 * 
 */
require_once('PHPUnit/Framework/TestCase.php');
require_once(PATH_t3lib.'class.t3lib_install.php');

class tx_phpunit_testcase extends PHPUnit_Framework_TestCase {

	protected $backupGlobals = false;


	/**
	 * Test database name
	 *
	 * @var string
	 */
	protected $testDatabase;


	public function __construct($name = null) {
		parent::__construct($name);

		$this->testDatabase = TYPO3_db.'_test';
	}

	/*
	 * Accesses the Typo3 database object, and uses it to fetch the list of databases. Then
	 * checks whether to a test database is already setup; if not, then creates it.
	 * 
	 * @return void
	 */
	protected function createDatabase() {
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (!in_array($this->testDatabase, $databaseNames)) {
			$db->admin_query('CREATE DATABASE '.$this->testDatabase);
		}
	}


 	/**
	 * Drops tables in test database
	 *
	 * @return void
	 */
	protected function cleanDatabase() {
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (in_array($this->testDatabase, $databaseNames)) {
			$db->sql_select_db($this->testDatabase);

			// drop all tables
			$tables = $this->getDatabaseTables();
			foreach($tables as $tableName) {
				$db->admin_query('DROP TABLE '. $tableName);
			}
		}
	}


	/**
	 * Drops test database
	 *
	 * @return void
	 */
	protected function dropDatabase() {
		$db = $GLOBALS['TYPO3_DB'];

		$databaseNames = $db->admin_get_dbs();

		if (in_array($this->testDatabase, $databaseNames)) {
			$db->sql_select_db($this->testDatabase);
			$db->admin_query('DROP DATABASE '.$this->testDatabase);
		}
	}


	/**
	 * Changes current database to test database
	 *
	 * @param string $databaseName	Overwrite test database name
	 * @return object
	 */
	protected function useTestDatabase($databaseName = null) {
		$db = $GLOBALS['TYPO3_DB'];

		if ($databaseName) {
			$database = $databaseName;
		} else {
			$database = $this->testDatabase;
		}

		$db->sql_select_db($database);

		return $db;
	}


	/**
	 * Import ext_tables.sql statements
	 *
	 * @param array $extensions Array containing extension keys
	 * @param boolean $importDependencies Wether to import dependency extensions
	 * @param array $skipDependencies Array containing extension keys to skip
	 * 
	 * @return void
	 */
	protected function importExtensions(array $extensions, $importDependencies=false, array &$skipDependencies=array()) {
		$this->useTestDatabase();

		foreach ($extensions as $extensionName) {
			// skip importing unloaded extensions and specified dependencies
			if (!t3lib_extMgm::isLoaded($extensionName) || in_array($extensionName, $skipDependencies)) {
				continue;
			}

			$skipDependencies = array_merge($skipDependencies, array($extensionName));

			if ($importDependencies) {
				$dependencies = $this->findDependencies($extensionName);
				if (is_array($dependencies)) {
					$this->importExtensions($dependencies, true, $skipDependencies);
				}
			}

			$this->importExtension($extensionName);
		}
	}


	protected function getDatabaseTables($databaseName = null) {
		$db = $this->useTestDatabase($databaseName);

		$tableNames = array();

		$res = $db->sql_query('show tables');
		while ($row = $db->sql_fetch_row($res)) {
			$tableNames[] = $row[0];
		}

		return $tableNames;
	}


	/**
	 * Import extension ext_tables.sql file
	 *
	 * @param string $extensionName
	 *
	 * @return void
	 */
	private function importExtension($extensionName) {
		// read sql file content
		$sqlFilename = t3lib_div::getFileAbsFileName(t3lib_extMgm::extPath($extensionName).'ext_tables.sql');
		$fileContent = t3lib_div::getUrl($sqlFilename);

		// find definitions
		$install = new t3lib_install;
		$FDfile = $install->getFieldDefinitions_sqlContent($fileContent);

		if (count($FDfile))	{
			// find statements to query
			$FDdatabase = $install->getFieldDefinitions_sqlContent($this->getTestDatabaseSchema());
			$diff = $install->getDatabaseExtra($FDfile, $FDdatabase);
			$updateStatements = $install->getUpdateSuggestions($diff);

			$updateTypes = array('add', 'change', 'create_table');

			foreach ($updateTypes as $updateType) {
				if (array_key_exists($updateType, $updateStatements)) {
					foreach((array)$updateStatements[$updateType] as $string) {
						$GLOBALS['TYPO3_DB']->admin_query($string);
					}
				}
			}
		}
	}


	/**
	 * Returns test database schema dump
	 *
	 * @return string
	 */
	private function getTestDatabaseSchema() {
		$db = $this->useTestDatabase();
		$tables = $this->getDatabaseTables();

		// find create statement for every table
		$linebreak =  chr(10);
		$schema = '';
		$db->sql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
		foreach ($tables as $tableName) {
			$res = $db->sql_query('show create table '. $tableName);
			$row = $db->sql_fetch_row($res);

			// modify statement to be accepted by TYPO3
			$createStatement = preg_replace('/ENGINE.*$/', '', $row[1]);
			$createStatement = preg_replace('/(CREATE TABLE.*\()/', $linebreak.'\\1'.$linebreak, $createStatement);
			$createStatement = preg_replace('/\) $/', $linebreak.')', $createStatement);

			$schema .= $createStatement. ';';
		}

		return $schema;
	}


	/**
	 * Returns array with extension names (dependencies)
	 *
	 * @param string $extKey
	 * @return array
	 */
	private function findDependencies($extKey) {
		$path = t3lib_div::getFileAbsFileName(t3lib_extMgm::extPath($extKey).'ext_emconf.php');
		// $_EXTKEY used as array key in EM_CONF in included file
		$_EXTKEY = $extKey;
		include($path);

		if(is_array($EM_CONF[$_EXTKEY]['constraints']['depends'])) {
			$dependencies = $EM_CONF[$_EXTKEY]['constraints']['depends'];

			// remove php and typo3 extension (not real extensions)
			if (isset($dependencies['php'])) {
				unset($dependencies['php']);
			}
			if (isset($dependencies['typo3'])) {
				unset($dependencies['typo3']);
			}

			return array_keys($dependencies);
		}

		return null;
	}


	/**
	 * Import dataset into test database
	 *
	 * @param string $path
	 * @return void
	 */
	protected function importDataSet($path) {
		$xml = simplexml_load_file($path);
		$db = $this->useTestDatabase();
		$foreignKeys = array();

		foreach ($xml->children() as $table) {
			$insertArray = array();

			foreach ($table->children() as $column) {
				$columnName = $column->getName();
				$columnValue = null;

				if (isset($column['ref'])) {
					list($tableName, $elementID) = explode('#', $column['ref']);
					$columnValue = $foreignKeys[$tableName][$elementID];
				}
				elseif (isset($column['is-null']) && $column['is-null']=='yes') {
					$columnValue = null;
				}
				else {
					$columnValue = $table->$columnName;
				}

				$insertArray[$columnName] = $columnValue;
			}

			$tableName = $table->getName();
			$db->exec_INSERTquery($tableName, $insertArray);

			if (isset($table['id'])) {
				$elementID = (string) $table['id'];
				$foreignKeys[$tableName][$elementID] = $db->sql_insert_id();
			}
		}
	}
}


/**
 * This class is provided for backwards compatibility with the extension t3unit
 * t3unit is based on PHPUnit version 2 (known as PHPUnit2)
 */
class tx_t3unit_testcase extends PHPUnit_Framework_TestCase {
}

?>