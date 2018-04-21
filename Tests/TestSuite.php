<?php
namespace OliverKlee\Phpunit\Tests;

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
        parent::__construct();

        $this->addTestFile(__DIR__ . '/tx_phpunit_test_testcase.php');
        $this->addTestFile(__DIR__ . '/database_testcase.php');
    }
}
