<?php

use TYPO3\CMS\Core\Cache\DatabaseSchemaService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Service\SqlExpectedSchemaService;
use TYPO3\CMS\Install\Service\SqlSchemaMigrationService;

/**
 * Database testcase base class.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_Database_TestCase extends \Tx_Phpunit_TestCase
{
    /**
     * name of a test database
     *
     * @var string
     */
    protected $testDatabase = '';

    /**
     * name of the original database name
     *
     * @var string
     */
    protected $originalDatabaseName = '';

    /**
     * Constructs a test case with the given name.
     *
     * @param string $name the name of a testcase
     * @param array $data ?
     * @param string $dataName ?
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        /** @var array $databaseConfiguration */
        $databaseConfiguration = $GLOBALS['TYPO3_CONF_VARS']['DB'];
        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8001000) {
            $this->originalDatabaseName = $databaseConfiguration['Connections'][ConnectionPool::DEFAULT_CONNECTION_NAME]['dbname'];
        } else {
            $this->originalDatabaseName = $databaseConfiguration['database'];
        }

        $this->testDatabase = strtolower($this->originalDatabaseName . '_test');
    }

    /**
     * Selects the TYPO3 database (again).
     *
     * If you have selected any non-TYPO3 in your unit tests, you need to
     * call this function in tearDown() in order to avoid problems with the
     * following unit tests and the TYPO3 back-end.
     *
     * @return void
     */
    protected function switchToTypo3Database()
    {
        $this->switchToOriginalTypo3Database(\Tx_Phpunit_Service_Database::getDatabaseConnection());
    }

    /**
     * Accesses the TYPO3 database instance and uses it to fetch the list of
     * available databases. Then this function creates a test database (if none
     * has been set up yet).
     *
     * @return bool
     *         TRUE if the database has been created successfully (or if there
     *         already is a test database), FALSE otherwise
     */
    protected function createDatabase()
    {
        $success = true;

        $this->dropDatabase();
        $db = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        $databaseNames = $db->admin_get_dbs();
        $this->switchToOriginalTypo3Database($db);

        if (!in_array($this->testDatabase, $databaseNames, true)
            && $db->admin_query('CREATE DATABASE `' . $this->testDatabase . '`') === false) {
            $success = false;
        }

        return $success;
    }

    /**
     * Drops all tables in the test database.
     *
     * @return void
     */
    protected function cleanDatabase()
    {
        $db = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        $databaseNames = $db->admin_get_dbs();
        $this->switchToOriginalTypo3Database($db);

        if (!in_array($this->testDatabase, $databaseNames, true)) {
            return;
        }

        $this->selectDatabase($this->testDatabase, $db);

        $tables = $this->getDatabaseTables();
        foreach ($tables as $tableName) {
            $db->admin_query('DROP TABLE `' . $tableName . '`');
        }
    }

    /**
     * Drops the test database.
     *
     * @return bool
     *         TRUE if the database has been dropped successfully, FALSE otherwise
     */
    protected function dropDatabase()
    {
        $db = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        $databaseNames = $db->admin_get_dbs();
        $this->switchToOriginalTypo3Database($db);

        if (!in_array($this->testDatabase, $databaseNames, true)) {
            return true;
        }

        $this->selectDatabase($this->testDatabase, $db);

        return $db->admin_query('DROP DATABASE `' . $this->testDatabase . '`') !== false;
    }

    /**
     * Sets the TYPO3 database instance to a test database.
     *
     * Note: This function does not back up the currenty TYPO3 database instance.
     *
     * @param string|null $databaseName
     *        the name of the test database to use; if none is provided, the
     *        name of the current TYPO3 database plus a suffix "_test" is used
     *
     * @return DatabaseConnection the test database
     * @throws Exception
     */
    protected function useTestDatabase($databaseName = null)
    {
        $db = \Tx_Phpunit_Service_Database::getDatabaseConnection();

        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8001000) {
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][ConnectionPool::DEFAULT_CONNECTION_NAME]['dbname'] = $this->testDatabase;
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = $this->testDatabase;
        }

        if (!$this->selectDatabase($databaseName ?: $this->testDatabase, $db)) {
            static::markTestSkipped('This test is skipped because the test database is not available.');
        }

        $this->setUpTestDatabase();
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);

        return $db;
    }

    /**
     * Selects the database depending on TYPO3 version.
     *
     * @param string $databaseName the name of the database to select
     * @param DatabaseConnection $database database object to process the change
     *
     * @return bool
     */
    protected function selectDatabase($databaseName, DatabaseConnection $database)
    {
        $database->setDatabaseName($databaseName);

        return $database->sql_select_db();
    }

    /**
     * Switch to the original database
     *
     * @param DatabaseConnection $databaseObject The database object
     *
     * @return void
     */
    protected function switchToOriginalTypo3Database($databaseObject)
    {
        $this->selectDatabase($this->originalDatabaseName, $databaseObject);
    }

    /**
     * Imports the ext_tables.sql statements from the given extensions.
     *
     * @param string[] $extensions
     *        keys of the extensions to import, may be empty
     * @param bool $importDependencies
     *        whether to import dependency extensions on which the given extensions
     *        depend as well
     * @param string[] &$skipDependencies
     *        keys of the extensions to skip, may be empty, will be modified
     *
     * @return void
     */
    protected function importExtensions(
        array $extensions,
        $importDependencies = false,
        array &$skipDependencies = []
    ) {
        $this->useTestDatabase();

        foreach ($extensions as $extensionName) {
            if (!ExtensionManagementUtility::isLoaded($extensionName)) {
                static::markTestSkipped(
                    'This test is skipped because the extension ' . $extensionName .
                    ' which was marked for import is not loaded on your system!'
                );
            } elseif (in_array($extensionName, $skipDependencies, true)) {
                continue;
            }

            $skipDependencies = array_merge($skipDependencies, [$extensionName]);

            if ($importDependencies) {
                $dependencies = $this->findDependencies($extensionName);
                if (is_array($dependencies)) {
                    $this->importExtensions($dependencies, true, $skipDependencies);
                }
            }

            $this->importExtension($extensionName);
        }

        // TODO: The hook should be replaced by real clean up and rebuild the whole
        // "TYPO3_CONF_VARS" in order to have a clean testing environment.
        // hook to load additional files
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'] as $file) {
                $sqlFilename = GeneralUtility::getFileAbsFileName($file);
                $fileContent = GeneralUtility::getUrl($sqlFilename);

                $this->importDatabaseDefinitions($fileContent);
            }
        }
    }

    /**
     * Gets the names of all tables in the database with the given name.
     *
     * @param string $databaseName
     *        the name of the database from which to retrieve the table names,
     *        if none is provided, the name of the current TYPO3 database plus a
     *        suffix "_test" is used
     *
     * @return string[]
     *         the names of all tables in the database $databaseName, might be empty
     */
    protected function getDatabaseTables($databaseName = null)
    {
        $db = $this->useTestDatabase($databaseName);

        $tableNames = [];

        $res = $db->sql_query('show tables');
        while (($row = $db->sql_fetch_row($res))) {
            /** @var array $row */
            $tableNames[] = $row[0];
        }

        return $tableNames;
    }

    /**
     * Imports the ext_tables.sql file of the extension with the given name
     * into the test database.
     *
     * @param string $extensionName
     *        the name of the installed extension to import, must not be empty
     *
     * @return void
     */
    private function importExtension($extensionName)
    {
        $sqlFilename =
            GeneralUtility::getFileAbsFileName(ExtensionManagementUtility::extPath($extensionName) . 'ext_tables.sql');
        $fileContent = GeneralUtility::getUrl($sqlFilename);

        $this->importDatabaseDefinitions($fileContent);
    }

    /**
     * Imports the data from the stddb tables.sql file.
     *
     * Example/intended usage:
     *
     * <pre>
     * protected function setUp() {
     *   $this->createDatabase();
     *   $db = $this->useTestDatabase();
     *   $this->importStdDB();
     *   $this->importExtensions(array('cms', 'static_info_tables', 'templavoila'));
     * }
     * </pre>
     *
     * @return void
     */
    protected function importStdDb()
    {
        // make sure missing caching framework tables do not get into the way
        $databaseSchemaService = GeneralUtility::makeInstance(DatabaseSchemaService::class);
        $cacheTables = $databaseSchemaService->getCachingFrameworkRequiredDatabaseSchema();
        $this->importDatabaseDefinitions($cacheTables);

        /* @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var SqlExpectedSchemaService $sqlExpectedSchemaService */
        $sqlExpectedSchemaService = $objectManager->get(SqlExpectedSchemaService::class);

        $databaseDefinitions = $sqlExpectedSchemaService->getTablesDefinitionString(true);

        $this->importDatabaseDefinitions($databaseDefinitions);
    }

    /**
     * Imports the SQL definitions from a (ext_)tables.sql file.
     *
     * @param string $definitionContent
     *        the SQL to import, must not be empty
     *
     * @return void
     */
    private function importDatabaseDefinitions($definitionContent)
    {
        /* @var SqlSchemaMigrationService $install */
        $install = GeneralUtility::makeInstance(SqlSchemaMigrationService::class);

        $fieldDefinitionsFile = $install->getFieldDefinitions_fileContent($definitionContent);
        if (empty($fieldDefinitionsFile)) {
            return;
        }

        // find statements to query
        $fieldDefinitionsDatabase = $install->getFieldDefinitions_fileContent($this->getTestDatabaseSchema());
        $diff = $install->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
        $updateStatements = $install->getUpdateSuggestions($diff);

        $updateTypes = ['add', 'change', 'create_table'];

        $databaseConnection = \Tx_Phpunit_Service_Database::getDatabaseConnection();
        foreach ($updateTypes as $updateType) {
            if (array_key_exists($updateType, $updateStatements)) {
                foreach ((array)$updateStatements[$updateType] as $string) {
                    $databaseConnection->admin_query($string);
                }
            }
        }
    }

    /**
     * Returns an SQL dump of the test database.
     *
     * @return string SQL dump of the test database, might be empty
     */
    private function getTestDatabaseSchema()
    {
        $db = $this->useTestDatabase();
        $tables = $this->getDatabaseTables();

        // finds create statement for every table
        $linefeed = chr(10);

        $schema = '';
        $db->sql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
        foreach ($tables as $tableName) {
            $res = $db->sql_query('show create table `' . $tableName . '`');
            /** @var array $row */
            $row = $db->sql_fetch_row($res);

            // modifies statement to be accepted by TYPO3
            $createStatement = preg_replace('/ENGINE.*$/', '', $row[1]);
            $createStatement = preg_replace(
                '/(CREATE TABLE.*\\()/',
                $linefeed . '\\1' . $linefeed,
                $createStatement
            );
            $createStatement = preg_replace('/\\) $/', $linefeed . ')', $createStatement);

            $schema .= $createStatement . ';';
        }

        return $schema;
    }

    /**
     * Finds all direct dependencies of the extension with the key $extKey.
     *
     * @param string $extKey the key of an installed extension, must not be empty
     *
     * @return string[]|null
     *         the keys of all extensions on which the given extension depends,
     *         will be NULL if the dependencies could not be determined
     */
    private function findDependencies($extKey)
    {
        $path = GeneralUtility::getFileAbsFileName(ExtensionManagementUtility::extPath($extKey) . 'ext_emconf.php');
        $_EXTKEY = $extKey;
        // This include is allowed. This is an exception in the TYPO3CMS standard.
        include $path;

        $dependencies = $EM_CONF[$_EXTKEY]['constraints']['depends'];
        if (!is_array($dependencies)) {
            return null;
        }

        // remove php and typo3 extension (not real extensions)
        if (isset($dependencies['php'])) {
            unset($dependencies['php']);
        }
        if (isset($dependencies['typo3'])) {
            unset($dependencies['typo3']);
        }

        return array_keys($dependencies);
    }

    /**
     * Imports a data set into the test database,
     *
     * @param string $path
     *        the absolute path to the XML file containing the data set to load
     *
     * @return void
     */
    protected function importDataSet($path)
    {
        $xml = simplexml_load_string(file_get_contents($path));
        $db = $this->useTestDatabase();
        $foreignKeys = [];

        /** @var SimpleXMLElement $table */
        foreach ($xml->children() as $table) {
            $insertArray = [];

            /** @var SimpleXMLElement $column */
            foreach ($table->children() as $column) {
                $columnName = $column->getName();
                $columnValue = null;

                if (isset($column['ref'])) {
                    list($tableName, $elementId) = explode('#', $column['ref']);
                    $columnValue = $foreignKeys[$tableName][$elementId];
                } elseif (isset($column['is-NULL']) && ($column['is-NULL'] === 'yes')) {
                    $columnValue = null;
                } else {
                    $columnValue = $table->$columnName;
                }

                $insertArray[$columnName] = $columnValue;
            }

            $tableName = $table->getName();
            $db->exec_INSERTquery($tableName, $insertArray);

            if (isset($table['id'])) {
                $elementId = (string)$table['id'];
                $foreignKeys[$tableName][$elementId] = $db->sql_insert_id();
            }
        }
    }

    /**
     * Populate $GLOBALS['TYPO3_DB'] and create test database
     *
     * @see https://github.com/Nimut/testing-framework/blob/master/src/TestingFramework/TestSystem/TestSystem.php
     *
     * @throws Exception
     * @return void
     */
    protected function setUpTestDatabase()
    {
        // The TYPO3 core misses to reset its internal connection state
        // This means we need to reset all connections to ensure database connection can be initialized
        $closure = \Closure::bind(function () {
            foreach (ConnectionPool::$connections as $connection) {
                $connection->close();
            }
            ConnectionPool::$connections = [];
        }, null, ConnectionPool::class);
        $closure();
    }

    /**
     * @param array $coreExtensions
     */
    protected function importCoreExtensionDefinitions(array $coreExtensions)
    {
        foreach ($coreExtensions as $coreExtension) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'][]
                = PATH_typo3 . 'sysext/' . $coreExtension . '/ext_tables.sql';
        }

        $this->importExtensions([]);
    }
}
