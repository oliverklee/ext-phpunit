<?php
namespace OliverKlee\Phpunit\Tests;

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
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */
class TestSuite extends \PHPUnit_Framework_TestSuite
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->addTestFile(dirname(__FILE__) . '/tx_phpunit_test_testcase.php');
        $this->addTestFile(dirname(__FILE__) . '/database_testcase.php');
    }
}
