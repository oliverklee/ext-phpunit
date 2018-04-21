<?php

/**
 * This interface provides functions for reading data from a POST/GET request.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_Request extends \Tx_Phpunit_Interface_ConvertService
{
    /**
     * @var string
     */
    const PARAMETER_NAMESPACE = 'tx_phpunit';

    /**
     * @var string
     */
    const PARAMETER_KEY_COMMAND = 'command';

    /**
     * @var string
     */
    const PARAMETER_KEY_TESTABLE = 'extSel';

    /**
     * @var string
     */
    const PARAMETER_KEY_TESTCASE = 'testCaseFile';

    /**
     * @var string
     */
    const PARAMETER_KEY_TEST = 'testname';

    /**
     * @var string
     */
    const PARAMETER_KEY_EXECUTE = 'bingo';
}
