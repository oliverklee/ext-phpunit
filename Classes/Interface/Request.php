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

/**
 * This interface provides functions for reading data from a POST/GET request.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_Request extends Tx_Phpunit_Interface_ConvertService
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