<?php
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * This class provides some static database-related functions (mostly convenience
 * wrappers).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_Database
{
    /**
     * page object which we will use to call enableFields on
     *
     * @var PageRepository
     */
    private static $pageForEnableFields = null;

    /**
     * cached results for the enableFields function
     *
     * @var array[]
     */
    private static $enableFieldsCache = [];

    /**
     * @var array[] cache for the results of existsTable with the table names
     *            as keys and the table SHOW STATUS information (in an array)
     *            as values
     */
    private static $tableNameCache = [];

    /**
     * cache for the results of hasTableColumn with the column names as keys and
     * the SHOW COLUMNS field information (in an array) as values
     *
     * @var array[]
     */
    private static $tableColumnCache = [];

    /**
     * cache for all TCA arrays
     *
     * @var array[]
     */
    private static $tcaCache = [];

    /**
     * Enables query logging in TYPO3's DB class.
     *
     * @return void
     */
    public static function enableQueryLogging()
    {
        self::getDatabaseConnection()->store_lastBuiltQuery = true;
    }

    /**
     * Wrapper function for \TYPO3\CMS\Frontend\Page\PageRepository::enableFields() since it is no
     * longer accessible statically.
     *
     * Returns a part of a WHERE clause which will filter out records with
     * start/end times or deleted/hidden/fe_groups fields set to values that
     * should de-select them according to the current time, preview settings or
     * user login.
     * Is using the $TCA arrays "ctrl" part where the key "enablefields"
     * determines for each table which of these features applies to that table.
     *
     * @param string $tableName
     *        table name found in the $TCA array
     * @param int $showHidden
     *        If $showHidden is set (0/1), any hidden-fields in records are
     *        ignored. NOTICE: If you call this function, consider what to do
     *        with the show_hidden parameter.
     * @param array $ignoreArray
     *        Array you can pass where keys can be "disabled", "starttime",
     *        "endtime", "fe_group" (keys from "enablefields" in TCA) and if set
     *        they will make sure that part of the clause is not added. Thus
     *        disables the specific part of the clause. For previewing etc.
     * @param bool $noVersionPreview
     *        If set, enableFields will be applied regardless of any versioning
     *        preview settings which might otherwise disable enableFields.
     *
     * @return string the WHERE clause starting like " AND ...=... AND ...=..."
     *
     * @throws InvalidArgumentException
     */
    public static function enableFields(
        $tableName,
        $showHidden = -1,
        array $ignoreArray = [],
        $noVersionPreview = false
    ) {
        if (!in_array($showHidden, [-1, 0, 1])) {
            throw new InvalidArgumentException(
                '$showHidden may only be -1, 0 or 1, but actually is ' . $showHidden,
                1331315445
            );
        }

        // maps $showHidden (-1..1) to (0..2) which ensures valid array keys
        $showHiddenKey = $showHidden + 1;
        $ignoresKey = serialize($ignoreArray);
        $previewKey = (int)$noVersionPreview;
        if (!isset(self::$enableFieldsCache[$tableName][$showHiddenKey][$ignoresKey][$previewKey])) {
            self::retrievePageForEnableFields();
            self::$enableFieldsCache[$tableName][$showHiddenKey][$ignoresKey][$previewKey]
                = self::$pageForEnableFields->enableFields(
                    $tableName,
                    $showHidden,
                    $ignoreArray,
                    $noVersionPreview
                );
        }

        return self::$enableFieldsCache[$tableName][$showHiddenKey][$ignoresKey][$previewKey];
    }

    /**
     * Makes sure that self::$pageForEnableFields is a page object.
     *
     * @return void
     */
    private static function retrievePageForEnableFields()
    {
        if (!is_object(self::$pageForEnableFields)) {
            if (isset($GLOBALS['TSFE'])
                && is_object($GLOBALS['TSFE']->sys_page)
            ) {
                self::$pageForEnableFields = $GLOBALS['TSFE']->sys_page;
            } else {
                self::$pageForEnableFields = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
            }
        }
    }

    /**
     * Recursively creates a comma-separated list of subpage UIDs from
     * a list of pages. The result also includes the original pages.
     * The maximum level of recursion can be limited:
     * 0 = no recursion (the default value, will return $startPages),
     * 1 = only direct child pages,
     * ...,
     * 250 = all descendants for all sane cases
     *
     * Note: The returned page list is _not_ sorted.
     *
     * @param string $startPages
     *        comma-separated list of page UIDs to start from, must only contain
     *        numbers and commas, may be empty
     * @param int $recursionDepth
     *        maximum depth of recursion, must be >= 0
     *
     * @return string
     *         comma-separated list of subpage UIDs including the  UIDs provided
     *         in $startPages, will be empty if $startPages is empty
     *
     * @throws InvalidArgumentException
     */
    public static function createRecursivePageList($startPages, $recursionDepth = 0)
    {
        if ($recursionDepth < 0) {
            throw new InvalidArgumentException('$recursionDepth must be >= 0.', 1331315492);
        }
        if ($recursionDepth == 0) {
            return (string)$startPages;
        }
        if ($startPages == '') {
            return '';
        }

        $dbResult = self::select(
            'uid',
            'pages',
            'pid IN (' . $startPages . ')' . self::enableFields('pages')
        );

        $subPages = [];
        $databaseConnection = self::getDatabaseConnection();
        while (($row = $databaseConnection->sql_fetch_assoc($dbResult))) {
            $subPages[] = $row['uid'];
        }
        $databaseConnection->sql_free_result($dbResult);

        if (!empty($subPages)) {
            $result = $startPages . ',' . self::createRecursivePageList(implode(',', $subPages), $recursionDepth - 1);
        } else {
            $result = $startPages;
        }

        return $result;
    }

    /*
     * Wrappers for common queries
     */

    /**
     * Executes a DELETE query.
     *
     * @param string $tableName
     *        the name of the table from which to delete, must not be empty
     * @param string $whereClause
     *        the WHERE clause to select the records, may be empty
     *
     * @return int the number of affected rows, might be 0
     *
     * @throws InvalidArgumentException
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function delete($tableName, $whereClause)
    {
        if ($tableName == '') {
            throw new InvalidArgumentException('The table name must not be empty.', 1331315508);
        }

        self::enableQueryLogging();
        $dbResult = self::getDatabaseConnection()->exec_DELETEquery(
            $tableName,
            $whereClause
        );
        if (!$dbResult) {
            throw new Tx_Phpunit_Exception_Database(1334439746);
        }

        return self::getDatabaseConnection()->sql_affected_rows();
    }

    /**
     * Executes an UPDATE query.
     *
     * @param string $tableName
     *        the name of the table to change, must not be empty
     * @param string $whereClause
     *        the WHERE clause to select the records, may be empty
     * @param array $fields
     *        key/value pairs of the fields to change, may be empty
     *
     * @return int the number of affected rows, might be 0
     *
     * @throws InvalidArgumentException
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function update($tableName, $whereClause, array $fields)
    {
        if ($tableName == '') {
            throw new InvalidArgumentException('The table name must not be empty.', 1331315523);
        }

        self::enableQueryLogging();
        $dbResult = self::getDatabaseConnection()->exec_UPDATEquery(
            $tableName,
            $whereClause,
            $fields
        );
        if (!$dbResult) {
            throw new Tx_Phpunit_Exception_Database(1334439755);
        }

        return self::getDatabaseConnection()->sql_affected_rows();
    }

    /**
     * Executes an INSERT query.
     *
     * @param string $tableName
     *        the name of the table in which the record should be created,
     *        must not be empty
     * @param array $recordData
     *        key/value pairs of the record to insert, must not be empty
     *
     * @return int
     *         the UID of the created record, will be 0 if the table has no UID column
     *
     * @throws InvalidArgumentException
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function insert($tableName, array $recordData)
    {
        if ($tableName == '') {
            throw new InvalidArgumentException('The table name must not be empty.', 1331315544);
        }
        if (empty($recordData)) {
            throw new InvalidArgumentException('$recordData must not be empty.', 1331315553);
        }

        self::enableQueryLogging();
        $dbResult = self::getDatabaseConnection()->exec_INSERTquery(
            $tableName,
            $recordData
        );
        if (!$dbResult) {
            throw new Tx_Phpunit_Exception_Database(1334439765);
        }

        return self::getDatabaseConnection()->sql_insert_id();
    }

    /**
     * Executes a SELECT query.
     *
     * @param string $fields
     *        list of fields to select, may be "*", must not be empty
     * @param string $tableNames
     *        comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause
     *        WHERE clause, may be empty
     * @param string $groupBy
     *        GROUP BY field(s), may be empty
     * @param string $orderBy
     *        ORDER BY field(s), may be empty
     * @param string $limit
     *        LIMIT value ([begin,]max), may be empty
     *
     * @return \mysqli_result MySQLi result object
     *
     * @throws InvalidArgumentException
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function select(
        $fields,
        $tableNames,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        if ($tableNames == '') {
            throw new InvalidArgumentException('The table names must not be empty.', 1331315600);
        }
        if ($fields == '') {
            throw new InvalidArgumentException('$fields must not be empty.', 1331315609);
        }

        self::enableQueryLogging();
        $dbResult = self::getDatabaseConnection()->exec_SELECTquery(
            $fields,
            $tableNames,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );
        if (!$dbResult) {
            throw new Tx_Phpunit_Exception_Database(1334439769);
        }

        return $dbResult;
    }

    /**
     * Executes a SELECT query and returns the single result row as an
     * associative array.
     *
     * If there is more than one matching record, only one will be returned.
     *
     * @param string $fields
     *        list of fields to select, may be "*", must not be empty
     * @param string $tableNames
     *        comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause
     *        WHERE clause, may be empty
     * @param string $groupBy
     *        GROUP BY field(s), may be empty
     * @param string $orderBy
     *        ORDER BY field(s), may be empty
     * @param int $offset
     *        the offset to start the result for, must be >= 0
     *
     * @return string[] the single result row, will not be empty
     *
     * @throws Tx_Phpunit_Exception_EmptyQueryResult if there is no matching record
     */
    public static function selectSingle(
        $fields,
        $tableNames,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $offset = 0
    ) {
        $result = self::selectMultiple(
            $fields,
            $tableNames,
            $whereClause,
            $groupBy,
            $orderBy,
            $offset . ',' . 1
        );
        if (empty($result)) {
            throw new Tx_Phpunit_Exception_EmptyQueryResult(1334439777);
        }

        return $result[0];
    }

    /**
     * Executes a SELECT query and returns the result rows as a two-dimensional
     * associative array.
     *
     * @param string $fieldNames
     *        list of fields to select, may be "*", must not be empty
     * @param string $tableNames
     *        comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause
     *        WHERE clause, may be empty
     * @param string $groupBy
     *        GROUP BY field(s), may be empty
     * @param string $orderBy
     *        ORDER BY field(s), may be empty
     * @param string $limit
     *        LIMIT value ([begin,]max), may be empty
     *
     * @return array[]
     *         the query result rows, will be empty if there are no matching records
     *
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function selectMultiple(
        $fieldNames,
        $tableNames,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        $result = [];
        $dbResult = self::select(
            $fieldNames,
            $tableNames,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );

        $databaseConnection = self::getDatabaseConnection();
        while (($recordData = $databaseConnection->sql_fetch_assoc($dbResult))) {
            $result[] = $recordData;
        }
        $databaseConnection->sql_free_result($dbResult);

        return $result;
    }

    /**
     * Executes a SELECT query and returns one column from the result rows as a
     * one-dimensional numeric array.
     *
     * If there is more than one matching record, only one will be returned.
     *
     * @param string $fieldName
     *        name of the field to select, must not be empty
     * @param string $tableNames
     *        comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause
     *        WHERE clause, may be empty
     * @param string $groupBy
     *        GROUP BY field(s), may be empty
     * @param string $orderBy
     *        ORDER BY field(s), may be empty
     * @param string $limit
     *        LIMIT value ([begin,]max), may be empty
     *
     * @return string[]
     *         one column from the the query result rows, will be empty if there
     *         are no matching records
     *
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function selectColumnForMultiple(
        $fieldName,
        $tableNames,
        $whereClause = '',
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        $rows = self::selectMultiple(
            $fieldName,
            $tableNames,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row[$fieldName];
        }

        return $result;
    }

    /**
     * Counts the number of matching records in the database for a particular
     * WHERE clause.
     *
     * @param string $tableNames
     *        comma-separated list of existing tables from which to count, can
     *        also be a JOIN, must not be empty
     * @param string $whereClause
     *        WHERE clause, may be empty
     *
     * @return int the number of matching records, will be >= 0
     *
     * @throws Tx_Phpunit_Exception_Database if an error has occurred
     */
    public static function count($tableNames, $whereClause = '')
    {
        $isOnlyOneTable = ((strpos($tableNames, ',') === false)
            && (stripos(trim($tableNames), ' JOIN ') === false));
        if ($isOnlyOneTable && self::tableHasColumnUid($tableNames)) {
            // Counting only the "uid" column is faster than counting *.
            $columns = 'uid';
        } else {
            $columns = '*';
        }

        $result = self::selectSingle(
            'COUNT(' . $columns . ') AS phpunit_counter',
            $tableNames,
            $whereClause
        );

        return (int)$result['phpunit_counter'];
    }

    /**
     * Checks whether there are any records in the table given by the first
     * parameter $tableName that match a given WHERE clause.
     *
     * @param string $tableName
     *        the name of the table to query, must not be empty
     * @param string $whereClause
     *        the WHERE part of the query, may be empty (all records will be
     *        counted in that case)
     *
     * @return bool
     *         TRUE if there is at least one matching record, FALSE otherwise
     */
    public static function existsRecord($tableName, $whereClause = '')
    {
        return self::count($tableName, $whereClause) > 0;
    }

    /**
     * Checks whether there is exactly one record in the table given by the
     * first parameter $tableName that matches a given WHERE clause.
     *
     * @param string $tableName
     *        the name of the table to query, must not be empty
     * @param string $whereClause
     *        the WHERE part of the query, may be empty (all records will be
     *        counted in that case)
     *
     * @return bool
     *         TRUE if there is exactly one matching record, FALSE otherwise
     */
    public static function existsExactlyOneRecord($tableName, $whereClause = '')
    {
        return self::count($tableName, $whereClause) == 1;
    }

    /**
     * Checks whether there is a record in the table given by the first
     * parameter $tableName that has the given UID.
     *
     * Important: This function also returns TRUE if there is a deleted or
     * hidden record with that particular UID.
     *
     * @param string $tableName
     *        the name of the table to query, must not be empty
     * @param int $uid
     *        the UID of the record to look up, must be > 0
     * @param string $additionalWhereClause
     *        additional WHERE clause to append, must either start with " AND"
     *        or be completely empty
     *
     * @return bool TRUE if there is a matching record, FALSE otherwise
     *
     * @throws InvalidArgumentException
     */
    public static function existsRecordWithUid(
        $tableName,
        $uid,
        $additionalWhereClause = ''
    ) {
        if ($uid <= 0) {
            throw new InvalidArgumentException('$uid must be > 0.', 1331315624);
        }

        return self::count($tableName, 'uid = ' . $uid . $additionalWhereClause) > 0;
    }

    /*
     * Functions concerning table names
     */

    /**
     * Returns a list of all table names that are available in the current
     * database.
     *
     * @return string[] list of table names
     */
    public static function getAllTableNames()
    {
        self::retrieveTableNames();

        return array_keys(self::$tableNameCache);
    }

    /**
     * Retrieves the table names of the current DB and stores them in
     * self::$tableNameCache.
     *
     * This function does nothing if the table names already have been
     * retrieved.
     *
     * @return void
     */
    private static function retrieveTableNames()
    {
        if (!empty(self::$tableNameCache)) {
            return;
        }

        self::$tableNameCache = self::getDatabaseConnection()->admin_get_tables();
    }

    /**
     * Checks whether a database table exists.
     *
     * @param string $tableName
     *        the name of the table to check for, must not be empty
     *
     * @return bool TRUE if the table $tableName exists, FALSE otherwise
     *
     * @throws InvalidArgumentException
     */
    public static function existsTable($tableName)
    {
        if ($tableName == '') {
            throw new InvalidArgumentException('The table name must not be empty.', 1331315636);
        }

        self::retrieveTableNames();

        return isset(self::$tableNameCache[$tableName]);
    }

    /*
     * Functions concerning the columns of a table
     */

    /**
     * Gets the column data for a table.
     *
     * @param string $tableName
     *        the name of the table for which the column names should be
     *        retrieved, must not be empty
     *
     * @return array
     *         the column data for the table $tableName with the column names as
     *         keys and the SHOW COLUMNS field information (in an array) as values
     */
    public static function getColumnsInTable($tableName)
    {
        self::retrieveColumnsForTable($tableName);

        return self::$tableColumnCache[$tableName];
    }

    /**
     * Gets the column definition for a field in $tableName.
     *
     * @param string $tableName
     *        the name of the table for which the column names should be
     *        retrieved, must not be empty
     * @param string $column
     *        the name of the field of which to retrieve the definition,
     *        must not be empty
     *
     * @return array
     *         the field definition for the field in $tableName, will not be empty
     */
    public static function getColumnDefinition($tableName, $column)
    {
        self::retrieveColumnsForTable($tableName);

        return self::$tableColumnCache[$tableName][$column];
    }

    /**
     * Retrieves and caches the column data for the table $tableName.
     *
     * If the column data for that table already is cached, this function does
     * nothing.
     *
     * @param string $tableName
     *        the name of the table for which the column names should be
     *        retrieved, must not be empty
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    private static function retrieveColumnsForTable($tableName)
    {
        if (!isset(self::$tableColumnCache[$tableName])) {
            if (!self::existsTable($tableName)) {
                throw new BadMethodCallException('The table "' . $tableName . '" does not exist.', 1331315659);
            }

            self::$tableColumnCache[$tableName] = self::getDatabaseConnection()->admin_get_fields($tableName);
        }
    }

    /**
     * Checks whether a table has a column with a particular name.
     *
     * To get a boolean TRUE as result, the table must contain a column with the
     * given name.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     * @param string $column
     *        the column name to check, must not be empty
     *
     * @return bool
     *         TRUE if the column with the provided name exists, FALSE otherwise
     */
    public static function tableHasColumn($tableName, $column)
    {
        if ($column == '') {
            return false;
        }

        self::retrieveColumnsForTable($tableName);

        return isset(self::$tableColumnCache[$tableName][$column]);
    }

    /**
     * Checks whether a table has a column "uid".
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool TRUE if a valid column was found, FALSE otherwise
     */
    public static function tableHasColumnUid($tableName)
    {
        return self::tableHasColumn($tableName, 'uid');
    }

    /*
     * Functions concerning the TCA
     */

    /**
     * Returns the TCA for a certain table.
     *
     * @param string $tableName
     *        the table name to look up, must not be empty
     *
     * @return array[] associative array with the TCA description for this table
     *
     * @throws BadMethodCallException
     */
    public static function getTcaForTable($tableName)
    {
        if (isset(self::$tcaCache[$tableName])) {
            return self::$tcaCache[$tableName];
        }

        if (!self::existsTable($tableName)) {
            throw new BadMethodCallException('The table "' . $tableName . '" does not exist.', 1331315679);
        }

        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new BadMethodCallException('The table "' . $tableName . '" has no TCA.', 1331315694);
        }
        self::$tcaCache[$tableName] = $GLOBALS['TCA'][$tableName];

        return self::$tcaCache[$tableName];
    }

    /**
     * Returns $GLOBALS['TYPO3_DB'].
     *
     * @return DatabaseConnection
     */
    public static function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
