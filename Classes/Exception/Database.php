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
use TYPO3\CMS\Core\Exception;

/**
 * This class represents an exception that should be thrown when a database
 * error has occurred.
 *
 * The exception automatically will use an error message, the error message
 * from the DB and the last query.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Exception_Database extends Exception
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
