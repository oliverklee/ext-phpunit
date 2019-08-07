<?php

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * This class represents an exception that should be thrown when a database
 * error has occurred.
 *
 * The exception automatically will use an error message, the error message
 * from the DB and the last query.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Exception_Database extends \Exception
{
    /**
     * The constructor.
     *
     * @param int $code error code, must be >= 0
     */
    public function __construct($code = 0)
    {
        /** @var DatabaseConnection $databaseConnection */
        $databaseConnection = $GLOBALS['TYPO3_DB'];

        $message = 'There was an error with the database query.' . LF . $databaseConnection->sql_error();

        if ($databaseConnection->store_lastBuiltQuery || $databaseConnection->debugOutput) {
            $message .= LF . 'The last built query:' . LF . $databaseConnection->debug_lastBuiltQuery;
        }

        parent::__construct($message, $code);
    }
}
