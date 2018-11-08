<?php

use TYPO3\CMS\Core\Exception;

/**
 * This class represents an exception that should be thrown when a database
 * query has an empty result, but should not have.
 *
 * The exception automatically will use an error message and the last query.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Exception_EmptyQueryResult extends Exception
{
    /**
     * The constructor.
     *
     * @param int $code error code, must be >= 0
     */
    public function __construct($code = 0)
    {
        $message = 'The database query returned an empty result, but should have returned a non-empty result.';

        if ($GLOBALS['TYPO3_DB']->store_lastBuiltQuery || $GLOBALS['TYPO3_DB']->debugOutput) {
            $message .= LF . 'The last built query:' . LF . $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
        }

        parent::__construct($message, $code);
    }
}
