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

use TYPO3\CMS\Core\Controller\CommandLineController;

defined('TYPO3_cliMode') or die('Access denied: CLI only.');

/**
 * Abstract TestRunner class. Can be used to implement other TestRunners which need CLI scope.
 *
 * Currently only CliTestRunner and IdeTestRunner are implemented.
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 */
abstract class Tx_Phpunit_TestRunner_AbstractCliTestRunner extends CommandLineController
{
    /**
     * Additional help text for the command line
     *
     * @var string[]
     */
    protected $additionalHelp = [];

    /**
     * definition of the extension name
     *
     * @var string
     */
    protected $extKey = 'phpunit_cli';

    /**
     * The constructor.
     */
    public function __construct()
    {
        setlocale(LC_NUMERIC, 'C');
        parent::__construct();

        $this->cli_help = array_merge(
            $this->cli_help,
            $this->additionalHelp
        );
    }
}
