<?php

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides various functions to handle dummy records in unit tests.
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Phpunit_Framework
{
    /**
     * @var int
     */
    const AUTO_INCREMENT_THRESHOLD = 100;

    /**
     * prefix of the extension for which this instance of the testing framework
     * was instantiated (e.g. "tx_seminars")
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * prefixes of additional extensions to which this instance of the testing
     * framework has access (e.g. "tx_seminars")
     *
     * @var string[]
     */
    protected $additionalTablePrefixes = [];

    /**
     * all own DB table names to which this instance of the testing framework
     * has access
     *
     * @var string[]
     */
    protected $ownAllowedTables = [];

    /**
     * all additional DB table names to which this instance of the testing
     * framework has access
     *
     * @var string[]
     */
    protected $additionalAllowedTables = [];

    /**
     * all system table names to which this instance of the testing framework
     * has access
     *
     * @var string[]
     */
    protected $allowedSystemTables = [
        'be_users',
        'fe_groups',
        'fe_users',
        'pages',
        'sys_template',
        'tt_content',
        'be_groups',
        'sys_file',
        'sys_file_collection',
        'sys_file_reference',
        'sys_category',
        'sys_category_record_mm',
    ];

    /**
     * all "dirty" non-system tables (i.e. all tables that were used for testing
     * and need to be cleaned up)
     *
     * @var string[]
     */
    protected $dirtyTables = [];

    /**
     * all "dirty" system tables (i.e. all tables that were used for testing and
     * need to be cleaned up)
     *
     * @var string[]
     */
    protected $dirtySystemTables = [];

    /**
     * sorting values of all relation tables
     *
     * @var array[]
     */
    protected $relationSorting = [];

    /**
     * The number of unusable UIDs after the maximum UID in a table before the auto increment value will be reset by
     * resetAutoIncrementLazily.
     *
     * This value needs to be high enough so that no two page UIDs will be the same within on request as the local
     * root-line cache of TYPO3 CMS otherwise might create false cache hits, causing failures for unit tests relying on
     * the root line.
     *
     * @var int
     */
    protected $resetAutoIncrementThreshold = 0;

    /**
     * the names of the created dummy files relative to the upload folder of the
     * extension to test
     *
     * @var string[]
     */
    protected $dummyFiles = [];

    /**
     * the names of the created dummy folders relative to the upload folder of
     * the extension to test
     *
     * @var string[]
     */
    protected $dummyFolders = [];

    /**
     * the absolute path to the upload folder of the extension to test (with the trailing slash)
     *
     * @var string
     */
    protected $uploadFolderPath = '';

    /**
     * whether a fake front end has been created
     *
     * @var bool
     */
    protected $hasFakeFrontEnd = false;

    /**
     * hook objects for this class
     *
     * @var \Tx_Phpunit_Interface_FrameworkCleanupHook[]
     */
    protected static $hooks = [];

    /**
     * whether the hooks in self::hooks have been retrieved
     *
     * @var bool
     */
    protected static $hooksHaveBeenRetrieved = false;

    /**
     * The constructor for this class.
     *
     * This testing framework can be instantiated for one extension at a time.
     * Example: In your testcase, you'll have something similar to this line of code:
     * $this->subject = new \Tx_Phpunit_Framework('tx_seminars');
     * The parameter you provide is the prefix of the table names of that particular
     * extension. Like this, we ensure that the testing framework creates and
     * deletes records only on table with this prefix.
     *
     * If you need dummy records on tables of multiple extensions, you'll have to
     * instantiate the testing frame work multiple times (once per extension).
     *
     * @param string $tablePrefix
     *        the table name prefix of the extension for which this instance of
     *        the testing framework should be used
     * @param string[] $additionalTablePrefixes
     *        the additional table name prefixes of the extensions for which
     *        this instance of the testing framework should be used, may be empty
     *
     * @throws \UnexpectedValueException if PATH_site is not defined
     */
    public function __construct($tablePrefix, array $additionalTablePrefixes = [])
    {
        if (!defined('PATH_site')) {
            throw new \UnexpectedValueException('PATH_site is not set.', 1476054703615);
        }

        $this->tablePrefix = $tablePrefix;
        $this->additionalTablePrefixes = $additionalTablePrefixes;
        $this->createListOfOwnAllowedTables();
        $this->createListOfAdditionalAllowedTables();
        $this->uploadFolderPath = PATH_site . 'uploads/' . $this->tablePrefix . '/';
        $this->determineAndSetAutoIncrementThreshold();

        /** @var array $rootLineCacheConfiguration */
        $rootLineCacheConfiguration = (array)$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_rootline'];
        $rootLineCacheConfiguration['backend'] = NullBackend::class;
        $rootLineCacheConfiguration['options'] = [];
        $cacheConfigurations = ['cache_rootline' => $rootLineCacheConfiguration];
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->setCacheConfigurations($cacheConfigurations);
    }

    /**
     * Determines a good value for the auto increment threshold and sets it.
     *
     * @return void
     */
    protected function determineAndSetAutoIncrementThreshold()
    {
        $resetAutoIncrementThreshold = self::AUTO_INCREMENT_THRESHOLD;

        $this->setResetAutoIncrementThreshold($resetAutoIncrementThreshold);
    }

    /**
     * Creates a new dummy record for unit tests.
     *
     * If no record data for the new array is given, an empty record will be
     * created. It will only contain a valid UID and the "is_dummy_record" flag
     * will be set to 1.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param string $tableName
     *        the name of the table on which the record should be created, must
     *        not be empty
     * @param array $recordData
     *        associative array that contains the data to save in the new
     *        record, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new record, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createRecord($tableName, array $recordData = [])
    {
        if (!$this->isNoneSystemTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.', 1334438817);
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334438963);
        }

        return $this->createRecordWithoutTableNameChecks($tableName, $recordData);
    }

    /**
     * Creates a new dummy record for unit tests without checks for the table
     * name.
     *
     * If no record data for the new array is given, an empty record will be
     * created. It will only contain a valid UID and the "is_dummy_record" flag
     * will be set to 1.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param string $tableName
     *        the name of the table on which the record should be created, must
     *        not be empty
     * @param array $recordData
     *        associative array that contains the data to save in the new
     *        record, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new record, will be > 0
     */
    protected function createRecordWithoutTableNameChecks($tableName, array $recordData)
    {
        $dummyColumnName = $this->getDummyColumnName($tableName);
        $recordData[$dummyColumnName] = 1;

        $uid = \Tx_Phpunit_Service_Database::insert($tableName, $recordData);
        $this->markTableAsDirty($tableName);

        return $uid;
    }

    /**
     * Creates a front-end page on the page with the UID given by the first
     * parameter $parentId.
     *
     * @param int $parentId
     *        UID of the page on which the page should be created
     * @param array $recordData
     *        associative array that contains the data to save in the new page,
     *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
     *
     * @return int the UID of the new page, will be > 0
     */
    public function createFrontEndPage($parentId = 0, array $recordData = [])
    {
        return $this->createGeneralPageRecord(1, $parentId, $recordData);
    }

    /**
     * Creates a system folder on the page with the UID given by the first
     * parameter $parentId.
     *
     * @param int $parentId
     *        UID of the page on which the system folder should be created
     * @param array $recordData
     *        associative array that contains the data to save in the new page,
     *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
     *
     * @return int the UID of the new system folder, will be > 0
     */
    public function createSystemFolder($parentId = 0, array $recordData = [])
    {
        return $this->createGeneralPageRecord(254, $parentId, $recordData);
    }

    /**
     * Creates a page record with the document type given by the first parameter
     * $documentType.
     *
     * The record will be created on the page with the UID given by the second
     * parameter $parentId.
     *
     * @param int $documentType
     *        document type of the record to create, must be > 0
     * @param int $parentId
     *        UID of the page on which the record should be created
     * @param array $recordData
     *        associative array that contains the data to save in the record,
     *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
     *
     * @return int the UID of the new record, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    protected function createGeneralPageRecord($documentType, $parentId, array $recordData)
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334438971);
        }
        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1334438980);
        }
        if (isset($recordData['doktype'])) {
            throw new \InvalidArgumentException('The column "doktype" must not be set in $recordData.', 1334438986);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $parentId;
        $completeRecordData['doktype'] = $documentType;

        return $this->createRecordWithoutTableNameChecks('pages', $completeRecordData);
    }

    /**
     * Creates a FE content element on the page with the UID given by the first
     * parameter $pageId.
     *
     * Created content elements are text elements by default, but the content
     * element's type can be overwritten by setting the key 'CType' in the
     * parameter $recordData.
     *
     * @param int $pageId
     *        UID of the page on which the content element should be created
     * @param array $recordData
     *        associative array that contains the data to save in the content
     *        element, may be empty, but must not contain the keys "uid" or "pid"
     *
     * @return int the UID of the new content element, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createContentElement($pageId = 0, array $recordData = [])
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439000);
        }
        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1334439007);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $pageId;
        if (!isset($completeRecordData['CType'])) {
            $completeRecordData['CType'] = 'text';
        }

        return $this->createRecordWithoutTableNameChecks('tt_content', $completeRecordData);
    }

    /**
     * Creates a template on the page with the UID given by the first parameter
     * $pageId.
     *
     * @param int $pageId
     *        UID of the page on which the template should be created, must be > 0
     * @param array $recordData
     *        associative array that contains the data to save in the new
     *        template, may be empty, but must not contain the keys "uid" or "pid"
     *
     * @return int the UID of the new template, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createTemplate($pageId, array $recordData = [])
    {
        if ($pageId <= 0) {
            throw new \InvalidArgumentException('$pageId must be > 0.', 1334439016);
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439024);
        }
        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1334439032);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $pageId;

        return $this->createRecordWithoutTableNameChecks('sys_template', $completeRecordData);
    }

    /**
     * Creates a FE user group.
     *
     * @param array $recordData
     *        associative array that contains the data to save in the new user
     *        group record, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new user group, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUserGroup(array $recordData = [])
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439042);
        }

        return $this->createRecordWithoutTableNameChecks('fe_groups', $recordData);
    }

    /**
     * Creates a FE user record.
     *
     * @param string $frontEndUserGroups
     *        comma-separated list of UIDs of the user groups to which the new
     *        user belongs, each must be > 0, may contain spaces, if empty a new
     *        FE user group will be created
     * @param array $recordData
     *        associative array that contains the data to save in the new user
     *        record, may be empty, but must not contain the keys "uid" or
     *        "usergroup"
     *
     * @return int the UID of the new FE user, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUser(
        $frontEndUserGroups = '',
        array $recordData = []
    ) {
        $frontEndUserGroupsWithoutSpaces = str_replace(' ', '', $frontEndUserGroups);

        if ($frontEndUserGroupsWithoutSpaces === '') {
            $frontEndUserGroupsWithoutSpaces = $this->createFrontEndUserGroup();
        }
        if (!preg_match('/^(?:[1-9]+\\d*,?)+$/', $frontEndUserGroupsWithoutSpaces)
        ) {
            throw new \InvalidArgumentException(
                '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.',
                1334439059
            );
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439065);
        }
        if (isset($recordData['usergroup'])) {
            throw new \InvalidArgumentException('The column "usergroup" must not be set in $recordData.', 1334439071);
        }

        $completeRecordData = $recordData;
        $completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

        return $this->createRecordWithoutTableNameChecks('fe_users', $completeRecordData);
    }

    /**
     * Creates and logs in an FE user.
     *
     * @param string $frontEndUserGroups
     *        comma-separated list of UIDs of the user groups to which the new
     *        user belongs, each must be > 0, may contain spaces; if empty a new
     *        front-end user group is created
     * @param array $recordData
     *        associative array that contains the data to save in the new user
     *        record, may be empty, but must not contain the keys "uid" or
     *        "usergroup"
     *
     * @return int the UID of the new FE user, will be > 0
     */
    public function createAndLoginFrontEndUser($frontEndUserGroups = '', array $recordData = [])
    {
        $frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);

        $this->loginFrontEndUser($frontEndUserUid);

        return $frontEndUserUid;
    }

    /**
     * Creates a BE user record.
     *
     * @param array $recordData
     *        associative array that contains the data to save in the new user
     *        record, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new BE user, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createBackEndUser(array $recordData = [])
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439081);
        }

        return $this->createRecordWithoutTableNameChecks('be_users', $recordData);
    }

    /**
     * Creates a BE user group.
     *
     * @param array $recordData
     *        associative array that contains the data to save in the new user
     *        group record, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new user group, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createBackEndUserGroup(array $recordData = [])
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1334439090);
        }

        return $this->createRecordWithoutTableNameChecks('be_groups', $recordData);
    }

    /**
     * Changes an existing dummy record and stores the new data for this
     * record. Only fields that get new values in $recordData will be changed,
     * everything else will stay untouched.
     *
     * The array with the new recordData must contain at least one entry, but
     * must not contain a new UID for the record. If you need to change the UID,
     * you have to create a new record!
     *
     * @param string $tableName
     *        the name of the table, must not be empty
     * @param int $uid
     *        the UID of the record to change, must not be empty
     * @param array $recordData
     *        associative array containing key => value pairs for those fields
     *        of the record that need to be changed, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function changeRecord($tableName, $uid, array $recordData)
    {
        $dummyColumnName = $this->getDummyColumnName($tableName);

        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The table "' . $tableName . '" is not on the lists with allowed tables.',
                1334439098
            );
        }
        if ($uid === 0) {
            throw new \InvalidArgumentException('The parameter $uid must not be zero.', 1334439105);
        }
        if (empty($recordData)) {
            throw new \InvalidArgumentException('The array with the new record data must not be empty.', 1334439111);
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException(
                'The parameter $recordData must not contain changes to the UID of a record.',
                1334439119
            );
        }
        if (isset($recordData[$dummyColumnName])) {
            throw new \InvalidArgumentException(
                'The parameter $recordData must not contain changes to the ' .
                'field "' . $dummyColumnName . '". It is impossible to ' .
                'convert a dummy record into a regular record.',
                1334439125
            );
        }
        if (!$this->countRecords($tableName, 'uid=' . $uid)) {
            throw new \Tx_Phpunit_Exception_Database(1334439132);
        }

        \Tx_Phpunit_Service_Database::update(
            $tableName,
            'uid = ' . $uid . ' AND ' . $dummyColumnName . ' = 1',
            $recordData
        );
    }

    /**
     * Deletes a dummy record from the database.
     *
     * Important: Only dummy records from non-system tables can be deleted with
     * this method. Should there for any reason exist a real record with that
     * UID, it would not be deleted.
     *
     * @param string $tableName
     *        name of the table from which the record should be deleted, must
     *        not be empty
     * @param int $uid
     *        UID of the record to delete, must be > 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function deleteRecord($tableName, $uid)
    {
        if (!$this->isNoneSystemTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.', 1334439187);
        }

        \Tx_Phpunit_Service_Database::delete(
            $tableName,
            'uid = ' . $uid . ' AND ' . $this->getDummyColumnName($tableName) . ' = 1'
        );
    }

    /**
     * Creates a relation between two records on different tables (so called
     * m:n relation).
     *
     * @param string $tableName
     *        name of the m:n table to which the record should be added, must
     *        not be empty
     * @param int $uidLocal
     *        UID of the local table, must be > 0
     * @param int $uidForeign
     *        UID of the foreign table, must be > 0
     * @param int $sorting
     *        sorting value of the relation, the default value is 0, which
     *        enables automatic sorting, a value >= 0 overwrites the automatic
     *        sorting
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function createRelation($tableName, $uidLocal, $uidForeign, $sorting = 0)
    {
        if (!$this->isNoneSystemTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.', 1334439196);
        }

        // Checks that the two given UIDs are valid.
        if ((int)$uidLocal <= 0) {
            throw new \InvalidArgumentException(
                '$uidLocal must be an integer > 0, but actually is "' . $uidLocal . '"',
                1334439206
            );
        }
        if ((int)$uidForeign <= 0) {
            throw new \InvalidArgumentException(
                '$uidForeign must be an integer > 0, but actually is "' . $uidForeign . '"',
                1334439213
            );
        }

        $this->markTableAsDirty($tableName);

        $recordData = [
            'uid_local' => $uidLocal,
            'uid_foreign' => $uidForeign,
            'sorting' => ($sorting > 0) ? $sorting : $this->getRelationSorting($tableName, $uidLocal),
            $this->getDummyColumnName($tableName) => 1,
        ];

        \Tx_Phpunit_Service_Database::insert($tableName, $recordData);
    }

    /**
     * Creates a relation between two records based on the rules defined in TCA
     * regarding the relation.
     *
     * @param string $tableName
     *        name of the table from which a relation should be created, must
     *        not be empty
     * @param int $uidLocal
     *        UID of the record in the local table, must be > 0
     * @param int $uidForeign
     *        UID of the record in the foreign table, must be > 0
     * @param string $columnName
     *        name of the column in which the relation counter should be
     *        updated, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function createRelationAndUpdateCounter(
        $tableName,
        $uidLocal,
        $uidForeign,
        $columnName
    ) {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
        }

        if ($uidLocal <= 0) {
            throw new \InvalidArgumentException(
                '$uidLocal must be > 0, but actually is "' . $uidLocal . '"',
                1334439220
            );
        }
        if ($uidForeign <= 0) {
            throw new \InvalidArgumentException(
                '$uidForeign must be  > 0, but actually is "' . $uidForeign . '"',
                1334439233
            );
        }

        $tca = \Tx_Phpunit_Service_Database::getTcaForTable($tableName);
        $relationConfiguration = $tca['columns'][$columnName];

        if (!isset($relationConfiguration['config']['MM']) || ($relationConfiguration['config']['MM'] === '')) {
            throw new Exception(
                'The column ' . $columnName . ' in the table ' . $tableName .
                ' is not configured to contain m:n relations using a m:n table.',
                1334439257
            );
        }

        if (!isset($relationConfiguration['config']['MM_opposite_field'])) {
            $this->createRelation(
                $relationConfiguration['config']['MM'],
                $uidLocal,
                $uidForeign
            );
        } else {
            // Switches the order of $uidForeign and $uidLocal as the relation
            // is the reverse part of a bidirectional relation.
            $this->createRelationAndUpdateCounter(
                $relationConfiguration['config']['foreign_table'],
                $uidForeign,
                $uidLocal,
                $relationConfiguration['config']['MM_opposite_field']
            );
        }

        $this->increaseRelationCounter($tableName, $uidLocal, $columnName);
    }

    /**
     * Deletes a dummy relation from an m:n table in the database.
     *
     * Important: Only dummy records can be deleted with this method. Should there
     * for any reason exist a real record with that combination of local and
     * foreign UID, it would not be deleted!
     *
     * @param string $tableName
     *        name of the table from which the record should be deleted, must
     *        not be empty
     * @param int $uidLocal
     *        UID on the local table, must be > 0
     * @param int $uidForeign
     *        UID on the foreign table, must be > 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function removeRelation($tableName, $uidLocal, $uidForeign)
    {
        if (!$this->isNoneSystemTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.', 1334439276);
        }

        \Tx_Phpunit_Service_Database::delete(
            $tableName,
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign .
            ' AND ' . $this->getDummyColumnName($tableName) . ' = 1'
        );
    }

    /**
     * Deletes all dummy records that have been added through this framework.
     * For this, all records with the "is_dummy_record" flag set to 1 will be
     * deleted from all tables that have been used within this instance of the
     * testing framework.
     *
     * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
     * which the current instance of the testing framework has access. Please
     * consider well, whether you want to do this as it's a huge performance
     * issue.
     *
     * @param bool $performDeepCleanUp
     *        whether a deep clean up should be performed
     *
     * @return void
     *
     * @throws Exception
     */
    public function cleanUp($performDeepCleanUp = false)
    {
        $this->cleanUpTableSet(false, $performDeepCleanUp);
        $this->cleanUpTableSet(true, $performDeepCleanUp);
        $this->deleteAllDummyFoldersAndFiles();
        $this->discardFakeFrontEnd();

        foreach ($this->getHooks() as $hook) {
            if (!($hook instanceof \Tx_Phpunit_Interface_FrameworkCleanupHook)) {
                throw new Exception(
                    'The class ' . get_class($hook) . ' must implement \\Tx_Phpunit_Interface_FrameworkCleanupHook.',
                    1299257923
                );
            }
            /** @var \Tx_Phpunit_Interface_FrameworkCleanupHook $hook */
            $hook->cleanUp();
        }

        RootlineUtility::purgeCaches();
    }

    /**
     * Deletes a set of records that have been added through this framework for
     * a set of tables (either the test tables or the allowed system tables).
     * For this, all records with the "is_dummy_record" flag set to 1 will be
     * deleted from all tables that have been used within this instance of the
     * testing framework.
     *
     * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
     * which the current instance of the testing framework has access. Please
     * consider well, whether you want to do this as it's a huge performance
     * issue.
     *
     * @param bool $useSystemTables
     *        whether to clean up the system tables (TRUE) or the non-system
     *        test tables (FALSE)
     * @param bool $performDeepCleanUp
     *        whether a deep clean up should be performed
     *
     * @return void
     */
    protected function cleanUpTableSet($useSystemTables, $performDeepCleanUp)
    {
        if ($useSystemTables) {
            $tablesToCleanUp = $performDeepCleanUp ? $this->allowedSystemTables : $this->dirtySystemTables;
        } else {
            $tablesToCleanUp = $performDeepCleanUp ? $this->ownAllowedTables : $this->dirtyTables;
        }

        foreach ($tablesToCleanUp as $currentTable) {
            $dummyColumnName = $this->getDummyColumnName($currentTable);

            // Runs a delete query for each allowed table. A
            // "one-query-deletes-them-all" approach was tested but we did not
            // find a working solution for that.
            \Tx_Phpunit_Service_Database::delete($currentTable, $dummyColumnName . ' = 1');

            // Resets the auto increment setting of the current table.
            $this->resetAutoIncrementLazily($currentTable);
        }

        // Resets the list of dirty tables.
        $this->dirtyTables = [];
    }

    /**
     * Deletes all dummy files and folders.
     *
     * @return void
     */
    protected function deleteAllDummyFoldersAndFiles()
    {
        // If the upload folder was created by the testing framework, it can be
        // removed at once.
        if (isset($this->dummyFolders['uploadFolder'])) {
            GeneralUtility::rmdir($this->getUploadFolderPath(), true);
            $this->dummyFolders = [];
            $this->dummyFiles = [];
        } else {
            foreach ($this->dummyFiles as $dummyFile) {
                $this->deleteDummyFile($dummyFile);
            }
            foreach ($this->dummyFolders as $dummyFolder) {
                $this->deleteDummyFolder($dummyFolder);
            }
        }
    }

    // ----------------------------------------------------------------------
    // File creation and deletion
    // ----------------------------------------------------------------------

    /**
     * Creates an empty dummy file with a unique file name in the calling
     * extension's upload directory.
     *
     * @param string $fileName
     *        path of the dummy file to create, relative to the calling
     *        extension's upload directory, must not be empty
     * @param string $content
     *        string content for the file to create, may be empty
     *
     * @return string
     *         the absolute path of the created dummy file, will not be empty
     *
     * @throws Exception
     */
    public function createDummyFile($fileName = 'test.txt', $content = '')
    {
        $this->createDummyUploadFolder();
        $uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);

        if (!GeneralUtility::writeFile($uniqueFileName, $content)) {
            throw new Exception('The file ' . $uniqueFileName . ' could not be created.', 1334439291);
        }

        $this->addToDummyFileList($uniqueFileName);

        return $uniqueFileName;
    }

    /**
     * Creates a dummy ZIP archive with a unique file name in the calling
     * extension's upload directory.
     *
     * @param string $fileName
     *        path of the dummy ZIP archive to create, relative to the calling
     *        extension's upload directory, must not be empty
     * @param string[] $filesToAddToArchive
     *        Absolute paths of the files to add to the ZIP archive. Note that
     *        the archives directory structure will be relative to the upload
     *        folder path, so only files within this folder or in sub-folders of
     *        this folder can be added.
     *        The provided array may be empty, but as ZIP archives cannot be
     *        empty, a content-less dummy text file will be added to the archive
     *        then.
     *
     * @return string
     *         the absolute path of the created dummy ZIP archive, will not be empty
     *
     * @throws Exception if the PHP installation does not provide ZIPArchive
     */
    public function createDummyZipArchive($fileName = 'test.zip', array $filesToAddToArchive = [])
    {
        $this->checkForZipArchive();

        $this->createDummyUploadFolder();
        $uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);
        $zip = new ZipArchive();

        if ($zip->open($uniqueFileName, ZipArchive::CREATE) !== true) {
            throw new Exception('The new ZIP archive "' . $fileName . '" could not be created.', 1334439299);
        }

        $contents = !empty($filesToAddToArchive) ? $filesToAddToArchive : [$this->createDummyFile()];

        foreach ($contents as $pathToFile) {
            if (!file_exists($pathToFile)) {
                throw new Exception(
                    'The provided path "' . $pathToFile . '" does not point to an existing file.',
                    1334439306
                );
            }
            $zip->addFile($pathToFile, $this->getPathRelativeToUploadDirectory($pathToFile));
        }

        $zip->close();
        $this->addToDummyFileList($uniqueFileName);

        return $uniqueFileName;
    }

    /**
     * Adds a file name to $this->dummyFiles.
     *
     * @param string $uniqueFileName
     *        file name to add, must be the unique name of a dummy file, must
     *        not be empty
     *
     * @return void
     */
    protected function addToDummyFileList($uniqueFileName)
    {
        $relativeFileName = $this->getPathRelativeToUploadDirectory($uniqueFileName);

        $this->dummyFiles[$relativeFileName] = $relativeFileName;
    }

    /**
     * Deletes the dummy file specified by the first parameter $fileName.
     *
     * @param string $fileName
     *        the path to the file to delete relative to
     *        $this->uploadFolderPath, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function deleteDummyFile($fileName)
    {
        $absolutePathToFile = $this->getUploadFolderPath() . $fileName;
        $fileExists = file_exists($absolutePathToFile);

        if (!isset($this->dummyFiles[$fileName])) {
            throw new \InvalidArgumentException(
                'The file "' . $absolutePathToFile . '" which you are trying to delete ' .
                (!$fileExists ? 'does not exist and has never been ' : 'was not ') .
                'created by this instance of the testing framework.',
                1334439315
            );
        }

        if ($fileExists && !unlink($absolutePathToFile)) {
            throw new Exception('The file "' . $absolutePathToFile . '" could not be deleted.', 1334439327);
        }

        unset($this->dummyFiles[$fileName]);
    }

    /**
     * Creates a dummy folder with a unique folder name in the calling
     * extension's upload directory.
     *
     * @param string $folderName
     *        name of the dummy folder to create relative to
     *        $this->uploadFolderPath, must not be empty
     *
     * @return string
     *         the absolute path of the created dummy folder, will not be empty
     *
     * @throws Exception
     */
    public function createDummyFolder($folderName)
    {
        $this->createDummyUploadFolder();
        $uniqueFolderName = $this->getUniqueFileOrFolderPath($folderName);

        if (!GeneralUtility::mkdir($uniqueFolderName)) {
            throw new Exception('The folder ' . $uniqueFolderName . ' could not be created.', 1334439333);
        }

        $relativeUniqueFolderName = $this->getPathRelativeToUploadDirectory($uniqueFolderName);

        // Adds the created dummy folder to the top of $this->dummyFolders so
        // it gets deleted before previously created folders through
        // $this->cleanUpFolders(). This is needed for nested dummy folders.
        $this->dummyFolders = [$relativeUniqueFolderName => $relativeUniqueFolderName] + $this->dummyFolders;

        return $uniqueFolderName;
    }

    /**
     * Deletes the dummy folder specified in the first parameter $folderName.
     * The folder must be empty (no files or subfolders).
     *
     * @param string $folderName
     *        the path to the folder to delete relative to
     *        $this->uploadFolderPath, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function deleteDummyFolder($folderName)
    {
        $absolutePathToFolder = $this->getUploadFolderPath() . $folderName;

        if (!is_dir($absolutePathToFolder)) {
            throw new \InvalidArgumentException(
                'The folder "' . $absolutePathToFolder . '" which you are trying to delete does not exist.',
                1334439343
            );
        }

        if (!isset($this->dummyFolders[$folderName])) {
            throw new \InvalidArgumentException(
                'The folder "' . $absolutePathToFolder . '" which you are trying to delete was not created by this instance of ' .
                'the testing framework.',
                1334439387
            );
        }

        if (!GeneralUtility::rmdir($absolutePathToFolder)) {
            throw new Exception('The folder "' . $absolutePathToFolder . '" could not be deleted.', 1334439393);
        }

        unset($this->dummyFolders[$folderName]);
    }

    /**
     * Creates the upload folder if it does not exist yet.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function createDummyUploadFolder()
    {
        $uploadFolderPath = $this->getUploadFolderPath();
        if (is_dir($uploadFolderPath)) {
            return;
        }

        $creationSuccessful = GeneralUtility::mkdir($uploadFolderPath);
        if (!$creationSuccessful) {
            throw new \RuntimeException(
                'The upload folder ' . $uploadFolderPath . ' could not be created.',
                1334439408
            );
        }

        $this->dummyFolders['uploadFolder'] = $uploadFolderPath;
    }

    /**
     * Sets the upload folder path.
     *
     * @param string $absolutePath
     *        absolute path to the folder where to work on during the tests, can
     *        be either an existing folder which will be cleaned up after the
     *        tests or a path of a folder to be created as soon as it is needed
     *        and deleted during cleanUp, must end with a trailing slash
     *
     * @return void
     *
     * @throws Exception
     *         if there are dummy files within the current upload folder as
     *         these files could not be deleted if the upload folder path has
     *         changed
     */
    public function setUploadFolderPath($absolutePath)
    {
        if (!empty($this->dummyFiles) || !empty($this->dummyFolders)) {
            throw new Exception(
                'The upload folder path must not be changed if there are already dummy files or folders.',
                1334439424
            );
        }

        $this->uploadFolderPath = $absolutePath;
    }

    /**
     * Returns the absolute path to the upload folder of the extension to test.
     *
     * @return string
     *         the absolute path to the upload folder of the extension to test,
     *         including the trailing slash
     */
    public function getUploadFolderPath()
    {
        return $this->uploadFolderPath;
    }

    /**
     * Returns the path relative to the calling extension's upload directory for
     * a path given in the first parameter $absolutePath.
     *
     * throws \InvalidArgumentException if the first parameter $absolutePath is not within
     * the calling extension's upload directory
     *
     * @param string $absolutePath
     *        the absolute path to process, must be within the calling extension's upload directory, must not be empty
     *
     * @return string the path relative to the calling extension's upload directory
     *
     * @throws \InvalidArgumentException
     */
    public function getPathRelativeToUploadDirectory($absolutePath)
    {
        if (!preg_match(
            '/^' . str_replace('/', '\\/', $this->getUploadFolderPath()) . '.*$/',
            $absolutePath
        )
        ) {
            throw new \InvalidArgumentException(
                'The first parameter $absolutePath is not within the calling extension\'s upload directory.',
                1334439445
            );
        }

        $encoding = mb_detect_encoding($this->getUploadFolderPath());
        $uploadFolderPathLength = mb_strlen($this->getUploadFolderPath(), $encoding);
        $absolutePathLength = mb_strlen($absolutePath, $encoding);

        return mb_substr($absolutePath, $uploadFolderPathLength, $absolutePathLength, $encoding);
    }

    /**
     * Returns a unique absolute path of a file or folder.
     *
     * @param string $path the path of a file or folder relative to the calling extension's upload directory,
     *                     must not be empty
     *
     * @return string the unique absolute path of a file or folder
     *
     * @throws \InvalidArgumentException
     */
    public function getUniqueFileOrFolderPath($path)
    {
        if ($path === '') {
            throw new \InvalidArgumentException('The first parameter $path must not be empty.', 1476054696353);
        }

        $pathInformation = pathinfo($path);
        $fileNameWithoutExtension = $pathInformation['filename'];
        if ($pathInformation['dirname'] !== '.') {
            $absoluteDirectoryWithTrailingSlash = $this->getUploadFolderPath() . $pathInformation['dirname'] . '/';
        } else {
            $absoluteDirectoryWithTrailingSlash = $this->getUploadFolderPath();
        }

        $extension = isset($pathInformation['extension']) ? ('.' . $pathInformation['extension']) : '';

        $suffixCounter = 0;
        do {
            $suffix = ($suffixCounter > 0) ? ('-' . $suffixCounter) : '';
            $newPath = $absoluteDirectoryWithTrailingSlash . $fileNameWithoutExtension . $suffix . $extension;
            $suffixCounter++;
        } while (is_file($newPath));

        return $newPath;
    }

    // ----------------------------------------------------------------------
    // Functions concerning a fake front end
    // ----------------------------------------------------------------------

    /**
     * Fakes a TYPO3 front end, using $pageUid as front-end page ID if provided.
     *
     * If $pageUid is zero, the UID of the start page of the current domain
     * will be used as page UID.
     *
     * This function creates $GLOBALS['TSFE'] and $GLOBALS['TT'].
     *
     * Note: This function does not set TYPO3_MODE to "FE" (because the value of
     * a constant cannot be changed after it has once been set).
     *
     * @param int $pageUid
     *        UID of a page record to use, must be >= 0
     *
     * @return int the UID of the used front-end page, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createFakeFrontEnd($pageUid = 0)
    {
        if ($pageUid < 0) {
            throw new \InvalidArgumentException('$pageUid must be >= 0.', 1334439467);
        }

        $this->suppressFrontEndCookies();
        $this->discardFakeFrontEnd();

        $this->registerNullPageCache();
        $GLOBALS['TT'] = GeneralUtility::makeInstance(NullTimeTracker::class);

        /** @var TypoScriptFrontendController $frontEnd */
        $frontEnd = GeneralUtility::makeInstance(TypoScriptFrontendController::class, $GLOBALS['TYPO3_CONF_VARS'], $pageUid, 0);
        $GLOBALS['TSFE'] = $frontEnd;

        // simulates a normal FE without any logged-in FE or BE user
        $frontEnd->beUserLogin = false;
        $frontEnd->renderCharset = 'utf-8';
        $frontEnd->workspacePreview = '';
        $frontEnd->initFEuser();
        $frontEnd->determineId();
        $frontEnd->initTemplate();
        $frontEnd->config = [];

        if (($pageUid > 0) && in_array('sys_template', $this->dirtySystemTables, true)) {
            $frontEnd->tmpl->runThroughTemplates($frontEnd->sys_page->getRootLine($pageUid), 0);
            $frontEnd->tmpl->generateConfig();
            $frontEnd->tmpl->loaded = 1;
            $frontEnd->settingLanguage();
            $frontEnd->settingLocale();
        }

        $frontEnd->newCObj();

        $this->hasFakeFrontEnd = true;
        $this->logoutFrontEndUser();

        return $frontEnd->id;
    }

    /**
     * Discards the fake front end.
     *
     * This function nulls out $GLOBALS['TSFE'] and $GLOBALS['TT']. In addition,
     * any logged-in front-end user will be logged out.
     *
     * The page record for the current front end will _not_ be deleted by this
     * function, though.
     *
     * If no fake front end has been created, this function does nothing.
     *
     * @return void
     */
    public function discardFakeFrontEnd()
    {
        if (!$this->hasFakeFrontEnd()) {
            return;
        }

        $this->logoutFrontEndUser();

        unset(
            $GLOBALS['TSFE']->tmpl,
            $GLOBALS['TSFE']->sys_page,
            $GLOBALS['TSFE']->fe_user,
            $GLOBALS['TSFE']->TYPO3_CONF_VARS,
            $GLOBALS['TSFE']->config,
            $GLOBALS['TSFE']->TCAcachedExtras,
            $GLOBALS['TSFE']->imagesOnPage,
            $GLOBALS['TSFE']->cObj,
            $GLOBALS['TSFE']->csConvObj,
            $GLOBALS['TSFE']->pagesection_lockObj,
            $GLOBALS['TSFE']->pages_lockObj
        );
        $GLOBALS['TSFE'] = null;
        $GLOBALS['TT'] = null;
        unset(
            $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'],
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserAuthentication::class]
        );

        $this->hasFakeFrontEnd = false;
    }

    /**
     * Returns whether this testing framework instance has a fake front end.
     *
     * @return bool
     *         TRUE if this instance has a fake front end, FALSE otherwise
     */
    public function hasFakeFrontEnd()
    {
        return $this->hasFakeFrontEnd;
    }

    /**
     * Makes sure that no FE login cookies will be sent.
     *
     * @return void
     */
    protected function suppressFrontEndCookies()
    {
        // avoid cookies from the phpMyAdmin extension
        $GLOBALS['PHP_UNIT_TEST_RUNNING'] = true;

        $GLOBALS['_POST']['FE_SESSION_KEY'] = '';
        $GLOBALS['_GET']['FE_SESSION_KEY'] = '';

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserAuthentication::class]
            = ['className' => \Tx_Phpunit_FrontEnd_UserWithoutCookies::class];
    }

    // ----------------------------------------------------------------------
    // FE user activities
    // ----------------------------------------------------------------------

    /**
     * Fakes that a front-end user has logged in.
     *
     * If a front-end user currently is logged in, he/she will be logged out
     * first.
     *
     * Note: To set the logged-in users group data properly, the front-end user
     *       and his groups must actually exist in the database.
     *
     * @param int $userId
     *        UID of the FE user, must not necessarily exist in the database,
     *        must be > 0
     *
     * @return void
     *
     * @throws Exception if no front end has been created
     * @throws \InvalidArgumentException
     */
    public function loginFrontEndUser($userId)
    {
        if ((int)$userId === 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1334439475);
        }
        if (!$this->hasFakeFrontEnd()) {
            throw new Exception('Please create a front end before calling loginFrontEndUser.', 1334439483);
        }

        if ($this->isLoggedIn()) {
            $this->logoutFrontEndUser();
        }

        $this->suppressFrontEndCookies();

        // With current TYPO3 versions we have to ensure an user id
        $tempUser = [
            $GLOBALS['TSFE']->fe_user->userid_column => $userId,
        ];
        $GLOBALS['TSFE']->fe_user->createUserSession($tempUser);
        $GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->getRawUserByUid($userId);
        $GLOBALS['TSFE']->fe_user->fetchGroupData();
        $GLOBALS['TSFE']->loginUser = 1;
    }

    /**
     * Logs out the current front-end user.
     *
     * If no front-end user is logged in, this function does nothing.
     *
     * @throws Exception if no front end has been created
     *
     * @return void
     */
    public function logoutFrontEndUser()
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new Exception('Please create a front end before calling logoutFrontEndUser.', 1334439488);
        }
        if (!$this->isLoggedIn()) {
            return;
        }

        $this->suppressFrontEndCookies();

        $GLOBALS['TSFE']->fe_user->logoff();
        $GLOBALS['TSFE']->loginUser = 0;
    }

    /**
     * Checks whether a FE user is logged in.
     *
     * @throws Exception if no front end has been created
     *
     * @return bool TRUE if a FE user is logged in, FALSE otherwise
     */
    public function isLoggedIn()
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new Exception('Please create a front end before calling isLoggedIn.', 1334439494);
        }

        return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])
            && is_array($GLOBALS['TSFE']->fe_user->user);
    }

    // ----------------------------------------------------------------------
    // Various helper functions
    // ----------------------------------------------------------------------

    /**
     * Generates a list of allowed tables to which this instance of the testing
     * framework has access to create/remove test records.
     *
     * The generated list is based on the list of all tables that TYPO3 can
     * access (which will be all tables in this database), filtered by prefix of
     * the extension to test.
     *
     * The array with the allowed table names is written directly to
     * $this->ownAllowedTables.
     *
     * @return void
     */
    protected function createListOfOwnAllowedTables()
    {
        $this->ownAllowedTables = [];
        $allTables = \Tx_Phpunit_Service_Database::getAllTableNames();
        $length = strlen($this->tablePrefix);

        foreach ($allTables as $currentTable) {
            if (substr_compare($this->tablePrefix, $currentTable, 0, $length) === 0) {
                $this->ownAllowedTables[] = $currentTable;
            }
        }
    }

    /**
     * Generates a list of additional allowed tables to which this instance of
     * the testing framework has access to create/remove test records.
     *
     * The generated list is based on the list of all tables that TYPO3 can
     * access (which will be all tables in this database), filtered by the
     * prefixes of additional extensions.
     *
     * The array with the allowed table names is written directly to
     * $this->additionalAllowedTables.
     *
     * @return void
     */
    protected function createListOfAdditionalAllowedTables()
    {
        $allTables = implode(',', \Tx_Phpunit_Service_Database::getAllTableNames());
        $additionalTablePrefixes = implode('|', $this->additionalTablePrefixes);

        $matches = [];

        preg_match_all(
            '/((' . $additionalTablePrefixes . ')_[a-z0-9]+[a-z0-9_]*)(,|$)/',
            $allTables,
            $matches
        );

        if (isset($matches[1])) {
            $this->additionalAllowedTables = $matches[1];
        }
    }

    /**
     * Checks whether the given table name is in the list of allowed tables for
     * this instance of the testing framework.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool
     *         TRUE if the name of the table is in the list of allowed tables,
     *         FALSE otherwise
     */
    protected function isOwnTableNameAllowed($tableName)
    {
        return in_array($tableName, $this->ownAllowedTables);
    }

    /**
     * Checks whether the given table name is in the list of additional allowed
     * tables for this instance of the testing framework.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool
     *         TRUE if the name of the table is in the list of additional
     *         allowed tables, FALSE otherwise
     */
    protected function isAdditionalTableNameAllowed($tableName)
    {
        return in_array($tableName, $this->additionalAllowedTables);
    }

    /**
     * Checks whether the given table name is in the list of allowed
     * system tables for this instance of the testing framework.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool
     *         TRUE if the name of the table is in the list of allowed system
     *         tables, FALSE otherwise
     */
    protected function isSystemTableNameAllowed($tableName)
    {
        return in_array($tableName, $this->allowedSystemTables);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables or
     * additional allowed tables for this instance of the testing framework.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool
     *         TRUE if the name of the table is in the list of allowed tables or
     *         additional allowed tables, FALSE otherwise
     */
    protected function isNoneSystemTableNameAllowed($tableName)
    {
        return $this->isOwnTableNameAllowed($tableName)
        || $this->isAdditionalTableNameAllowed($tableName);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables,
     * additional allowed tables or allowed system tables.
     *
     * @param string $tableName
     *        the name of the table to check, must not be empty
     *
     * @return bool
     *         TRUE if the name of the table is in the list of allowed tables,
     *         additional allowed tables or allowed system tables, FALSE otherwise
     */
    protected function isTableNameAllowed($tableName)
    {
        return $this->isNoneSystemTableNameAllowed($tableName)
        || $this->isSystemTableNameAllowed($tableName);
    }

    /**
     * Returns the name of the column that marks a record as a dummy record.
     *
     * On most tables this is "is_dummy_record", but on system tables like
     * "pages" or "fe_users", the column is called "tx_phpunit_dummy_record".
     *
     * On additional tables, the column is built using $this->tablePrefix as
     * prefix e.g. "tx_seminars_is_dummy_record" if $this->tablePrefix =
     * "tx_seminars".
     *
     * @param string $tableName
     *        the table name to look up, must not be empty
     *
     * @return string the name of the column that marks a record as dummy record
     */
    public function getDummyColumnName($tableName)
    {
        $result = 'is_dummy_record';

        if ($this->isSystemTableNameAllowed($tableName)) {
            $result = 'tx_phpunit_' . $result;
        } elseif ($this->isAdditionalTableNameAllowed($tableName)) {
            $result = $this->tablePrefix . '_' . $result;
        }

        return $result;
    }

    /**
     * Counts the dummy records in the table given by the first parameter $tableName
     * that match a given WHERE clause.
     *
     * @param string $tableName
     *        the name of the table to query, must not be empty
     * @param string $whereClause
     *        the WHERE part of the query, may be empty (all records will be
     *        counted in that case)
     *
     * @return int the number of records that have been found, will be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function countRecords($tableName, $whereClause = '')
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439501
            );
        }

        $whereForDummyColumn = $this->getDummyColumnName($tableName) . ' = 1';
        $compoundWhereClause = ($whereClause !== '')
            ? '(' . $whereClause . ') AND ' . $whereForDummyColumn : $whereForDummyColumn;

        return \Tx_Phpunit_Service_Database::count($tableName, $compoundWhereClause);
    }

    /**
     * Checks whether there are any dummy records in the table given by the
     * first parameter $tableName that match a given WHERE clause.
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
    public function existsRecord($tableName, $whereClause = '')
    {
        return $this->countRecords($tableName, $whereClause) > 0;
    }

    /**
     * Checks whether there is a dummy record in the table given by the first
     * parameter $tableName that has the given UID.
     *
     * @param string $tableName
     *        the name of the table to query, must not be empty
     * @param int $uid
     *        the UID of the record to look up, must be > 0
     *
     * @return bool TRUE if there is a matching record, FALSE otherwise
     *
     * @throws \InvalidArgumentException
     */
    public function existsRecordWithUid($tableName, $uid)
    {
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$uid must be > 0.', 1334439512);
        }

        return $this->countRecords($tableName, 'uid = ' . $uid) > 0;
    }

    /**
     * Checks whether there is exactly one dummy record in the table given by
     * the first parameter $tableName that matches a given WHERE clause.
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
    public function existsExactlyOneRecord($tableName, $whereClause = '')
    {
        return $this->countRecords($tableName, $whereClause) === 1;
    }

    /**
     * Eagerly resets the auto increment value for a given table to the highest
     * existing UID + 1.
     *
     * @param string $tableName
     *        the name of the table on which we're going to reset the auto
     *        increment entry, must not be empty
     *
     * @see resetAutoIncrementLazily
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function resetAutoIncrement($tableName)
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439521
            );
        }

        // Checks whether the current table qualifies for this method. If there
        // is no column "uid" that has the "auto_increment" flag set, we should
        // not try to reset this inexistent auto increment index to avoid DB
        // errors.
        if (!Tx_Phpunit_Service_Database::tableHasColumnUid($tableName)) {
            return;
        }

        $newAutoIncrementValue = $this->getMaximumUidFromTable($tableName) + 1;

        \Tx_Phpunit_Service_Database::enableQueryLogging();
        // Updates the auto increment index for this table. The index will be
        // set to one UID above the highest existing UID.
        $dbResult = \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_query(
            'ALTER TABLE ' . $tableName . ' AUTO_INCREMENT=' . $newAutoIncrementValue . ';'
        );
        if ($dbResult === false) {
            throw new \Tx_Phpunit_Exception_Database(1334439540);
        }
    }

    /**
     * Resets the auto increment value for a given table to the highest existing
     * UID + 1 if the current auto increment value is higher than a certain
     * threshold over the current maximum UID.
     *
     * The threshold is 100 by default and can be set using
     * setResetAutoIncrementThreshold.
     *
     * @param string $tableName
     *        the name of the table on which we're going to reset the auto
     *        increment entry, must not be empty
     *
     * @see resetAutoIncrement
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function resetAutoIncrementLazily($tableName)
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439548
            );
        }

        // Checks whether the current table qualifies for this method. If there
        // is no column "uid" that has the "auto_increment" flag set, we should
        // not try to reset this inexistent auto increment index to avoid
        // database errors.
        if (!Tx_Phpunit_Service_Database::tableHasColumnUid($tableName)) {
            return;
        }

        if ($this->getAutoIncrement($tableName) > ($this->getMaximumUidFromTable($tableName) + $this->resetAutoIncrementThreshold)) {
            $this->resetAutoIncrement($tableName);
        }
    }

    /**
     * Sets the threshold for resetAutoIncrementLazily.
     *
     * @param int $threshold
     *        threshold, must be > 0
     *
     * @see resetAutoIncrementLazily
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setResetAutoIncrementThreshold($threshold)
    {
        if ($threshold <= 0) {
            throw new \InvalidArgumentException('$threshold must be > 0.', 1334439558);
        }

        $this->resetAutoIncrementThreshold = $threshold;
    }

    /**
     * Reads the highest UID for a database table.
     *
     * This function may only be called after that the provided table name
     * has been checked to be non-empty, valid and pointing to an existing
     * database table that has the "uid" column.
     *
     * @param string $tableName
     *        the name of an existing table that has the "uid" column
     *
     * @return int the highest UID from this table, will be >= 0
     */
    protected function getMaximumUidFromTable($tableName)
    {
        $row = \Tx_Phpunit_Service_Database::selectSingle('MAX(uid) AS uid', $tableName);

        return (int)$row['uid'];
    }

    /**
     * Reads the current auto increment value for a given table.
     *
     * This function is only valid for tables that actually have an auto
     * increment value.
     *
     * @param string $tableName
     *        the name of the table for which the auto increment value should be
     *        retrieved, must not be empty
     *
     * @return int
     *         the current auto_increment value of table $tableName, will be > 0
     *
     * @throws \InvalidArgumentException
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function getAutoIncrement($tableName)
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439567
            );
        }

        \Tx_Phpunit_Service_Database::enableQueryLogging();
        $dbResult = \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_query(
            'SHOW TABLE STATUS WHERE Name = \'' . $tableName . '\';'
        );
        if (!$dbResult) {
            throw new \Tx_Phpunit_Exception_Database(1334439578);
        }

        $row = \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_fetch_assoc($dbResult);
        \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_free_result($dbResult);

        $autoIncrement = $row['Auto_increment'];
        if ($autoIncrement === null) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439584
            );
        }

        return (int)$autoIncrement;
    }

    /**
     * Returns the list of allowed table names.
     *
     * @return string[]
     *         all allowed table names for this instance of the testing framework
     */
    public function getListOfOwnAllowedTableNames()
    {
        return $this->ownAllowedTables;
    }

    /**
     * Returns the list of additional allowed table names.
     *
     * @return string[]
     *         all additional allowed table names for this instance of the
     *         testing framework, may be empty
     */
    public function getListOfAdditionalAllowedTableNames()
    {
        return $this->additionalAllowedTables;
    }

    /**
     * Puts one or multiple table names on the list of dirty tables (which
     * represents a list of tables that were used for testing and contain dummy
     * records and thus are called "dirty" until the next clean up).
     *
     * @param string $tableNames
     *        the table name or a comma-separated list of table names to put on
     *        the list of dirty tables, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function markTableAsDirty($tableNames)
    {
        foreach (GeneralUtility::trimExplode(',', $tableNames) as $currentTable) {
            if ($this->isNoneSystemTableNameAllowed($currentTable)) {
                $this->dirtyTables[$currentTable] = $currentTable;
            } elseif ($this->isSystemTableNameAllowed($currentTable)) {
                $this->dirtySystemTables[$currentTable] = $currentTable;
            } else {
                throw new \InvalidArgumentException(
                    'The table name "' . $currentTable . '" is not allowed for markTableAsDirty.',
                    1334439595
                );
            }
        }
    }

    /**
     * Returns the list of tables that contain dummy records from testing. These
     * tables are called "dirty tables" as they need to be cleaned up.
     *
     * @return string[]
     *         associative array containing names of database tables that need
     *         to be cleaned up
     */
    public function getListOfDirtyTables()
    {
        return $this->dirtyTables;
    }

    /**
     * Returns the list of system tables that contain dummy records from
     * testing. These tables are called "dirty tables" as they need to be
     * cleaned up.
     *
     * @return string[]
     *         associative array containing names of system database tables that
     *         need to be cleaned up
     */
    public function getListOfDirtySystemTables()
    {
        return $this->dirtySystemTables;
    }

    /**
     * Returns the next sorting value of the relation table which should be used.
     *
     * Note: This function does not take already existing relations in the
     * database (which were created without using the testing framework) into
     * account. So you always should create new dummy records and create a
     * relation between these two dummy records, so you're sure there are not
     * already relations for a local UID in the database.
     *
     * @param string $tableName
     *        the relation table, must not be empty
     * @param int $uidLocal
     *        UID of the local table, must be > 0
     *
     * @return int the next sorting value to use (> 0)
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=1423
     */
    public function getRelationSorting($tableName, $uidLocal)
    {
        if (!$this->relationSorting[$tableName][$uidLocal]) {
            $this->relationSorting[$tableName][$uidLocal] = 0;
        }

        $this->relationSorting[$tableName][$uidLocal]++;

        return $this->relationSorting[$tableName][$uidLocal];
    }

    /**
     * Updates an int field of a database table by one. This is mainly needed
     * for counting up the relation counter when creating a database relation.
     *
     * The field to update must be of type int.
     *
     * @param string $tableName
     *        name of the table, must not be empty
     * @param int $uid
     *        the UID of the record to modify, must be > 0
     * @param string $fieldName
     *        the field name of the field to modify, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \Tx_Phpunit_Exception_Database
     */
    public function increaseRelationCounter($tableName, $uid, $fieldName)
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The table name "' . $tableName .
                '" is invalid. This means it is either empty or not in the list of allowed tables.',
                1334439601
            );
        }
        if (!\Tx_Phpunit_Service_Database::tableHasColumn($tableName, $fieldName)) {
            throw new \InvalidArgumentException(
                'The table ' . $tableName . ' has no column ' . $fieldName . '.',
                1334439616
            );
        }

        \Tx_Phpunit_Service_Database::enableQueryLogging();
        $dbResult = \Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_query(
            'UPDATE ' . $tableName . ' SET ' . $fieldName . '=' . $fieldName . '+1 WHERE uid=' . $uid
        );
        if (!$dbResult) {
            throw new \Tx_Phpunit_Exception_Database(1334439623);
        }

        if (\Tx_Phpunit_Service_Database::getDatabaseConnection()->sql_affected_rows() === 0) {
            throw new \Tx_Phpunit_Exception_Database(1334439632);
        }

        $this->markTableAsDirty($tableName);
    }

    /**
     * Checks whether the ZIPArchive class is provided by the PHP installation.
     *
     * Note: This function can be used to mark tests as skipped if this class is
     *       not available but required for a test to pass succesfully.
     *
     * @throws Exception if the PHP installation does not provide ZIPArchive
     *
     * @return void
     */
    public function checkForZipArchive()
    {
        if (!in_array('zip', get_loaded_extensions(), true)) {
            throw new Exception('This PHP installation does not provide the ZIPArchive class.', 1334439642);
        }
    }

    /**
     * Gets all hooks for this class.
     *
     * @return \Tx_Phpunit_Interface_FrameworkCleanupHook[] the hook objects, will be empty if no hooks have been set
     */
    protected function getHooks()
    {
        if (!self::$hooksHaveBeenRetrieved) {
            $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp'];
            if (is_array($hookClasses)) {
                foreach ($hookClasses as $hookClass) {
                    self::$hooks[] = GeneralUtility::getUserObj($hookClass);
                }
            }

            self::$hooksHaveBeenRetrieved = true;
        }

        return self::$hooks;
    }

    /**
     * Purges the cached hooks.
     *
     * @return void
     */
    public function purgeHooks()
    {
        self::$hooks = [];
        self::$hooksHaveBeenRetrieved = false;
    }

    /**
     * Returns the current front-end instance.
     *
     * This method must only be called when there is a front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    protected function getFrontEnd()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return void
     */
    private function registerNullPageCache()
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        if ($cacheManager->hasCache('cache_pages')) {
            return;
        }

        /** @var NullBackend $backEnd */
        $backEnd = GeneralUtility::makeInstance(NullBackend::class, 'Testing');
        /** @var VariableFrontend $cache */
        $frontEnd = GeneralUtility::makeInstance(VariableFrontend::class, 'cache_pages', $backEnd);
        $cacheManager->registerCache($frontEnd);
    }
}
