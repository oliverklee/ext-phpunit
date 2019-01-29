<?php

/**
 * This class runs PHPUnit in CLI mode and includes the PHP boot script of an IDE.
 *
 * @deprecated Will be removed in PHPUnit 6.
 *
 * @author Helmut Hummel <helmut.hummel@typo3.org>
 */
class Tx_Phpunit_TestRunner_IdeTestRunner extends \Tx_Phpunit_TestRunner_AbstractCliTestRunner
{
    /**
     * Additional help text for the command line
     *
     * @var string[]
     */
    protected $additionalHelp = [
        'name' => 'Tx_Phpunit_TestRunner_IdeTestRunner',
        'synopsis' => 'phpunit_ide_testrunner <test or test folder> ###OPTIONS###',
        'description' => 'This script should only be run through an IDE.',
        'examples' => '',
        'author' => '(c) 2012-2013 Helmut Hummel <helmut.hummel@typo3.org>',
    ];
}
