<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_TestCaseService
     */
    protected $subject = null;

    /**
     * the absolute path to the fixtures directory for this test case
     *
     * @var string
     */
    private $fixturesPath = '';

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $userSettingsService = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_Service_TestCaseService();

        $this->userSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectUserSettingsService($this->userSettingsService);

        $this->fixturesPath = ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Service/Fixtures/';
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(
            'TYPO3\\CMS\\Core\\SingletonInterface',
            $this->subject
        );
    }


    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function findTestCaseFilesInDirectoryForEmptyPathThrowsException()
    {
        $this->subject->findTestCaseFilesInDirectory('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function findTestCaseFilesInDirectoryForInexistentPathThrowsException()
    {
        $this->subject->findTestCaseFilesInDirectory(
            $this->fixturesPath . 'DoesNotExist/'
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryForEmptyDirectoryReturnsEmptyArray()
    {
        self::assertSame(
            array(),
            $this->subject->findTestCaseFilesInDirectory($this->fixturesPath . 'Empty/')
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryFindsFileWithProperTestcaseFileName()
    {
        $path = 'OneTest.php';

        /** @var \Tx_Phpunit_Service_TestCaseService|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            'Tx_Phpunit_Service_TestCaseService',
            array('isNotFixturesPath', 'isTestCaseFileName')
        );
        $subject->expects(self::any())->method('isNotFixturesPath')->will((self::returnValue(true)));
        $subject->expects(self::at(1))->method('isTestCaseFileName')
            ->with($this->fixturesPath . $path)->will(self::returnValue(true));

        self::assertContains(
            $path,
            $subject->findTestCaseFilesInDirectory($this->fixturesPath)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryNotFindsFileWithNonProperTestcaseFileName()
    {
        $path = 'OneTest.php';

        /** @var \Tx_Phpunit_Service_TestCaseService|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            'Tx_Phpunit_Service_TestCaseService',
            array('isNotFixturesPath', 'isTestCaseFileName')
        );
        $subject->expects(self::any())->method('isNotFixturesPath')->will((self::returnValue(true)));
        $subject->expects(self::at(1))->method('isTestCaseFileName')
            ->with($this->fixturesPath . $path)->will(self::returnValue(false));

        self::assertNotContains(
            $path,
            $subject->findTestCaseFilesInDirectory($this->fixturesPath)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryFindsTestcaseInSubfolder()
    {
        $path = 'Service/TestFinderTest.php';

        self::assertContains(
            $path,
            $this->subject->findTestCaseFilesInDirectory(ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/')
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryAcceptsPathWithTrailingSlash()
    {
        $result = $this->subject->findTestCaseFilesInDirectory(ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Service');

        self::assertFalse(
            empty($result)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryAcceptsPathWithoutTrailingSlash()
    {
        $result = $this->subject->findTestCaseFilesInDirectory(
            ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Service'
        );

        self::assertFalse(
            empty($result)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectorySortsFileNamesInAscendingOrder()
    {
        $result = $this->subject->findTestCaseFilesInDirectory(ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Service/');

        $fileName1 = 'DatabaseTest.php';
        $fileName2 = 'TestFinderTest.php';

        self::assertTrue(
            array_search($fileName1, $result) < array_search($fileName2, $result)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryNotFindsFixtureClassesWithUppercasePath()
    {
        $path = 'OneTest.php';

        self::assertNotContains(
            $path,
            $this->subject->findTestCaseFilesInDirectory($this->fixturesPath)
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryNotFindsFixtureClassesWithLowercasePath()
    {
        if (!ExtensionManagementUtility::isLoaded('aaa')) {
            self::markTestSkipped(
                'This test can only be run if the extension "aaa" from TestExtensions/ is installed.'
            );
        }

        $path = ExtensionManagementUtility::extPath('aaa') . 'Tests/Unit/';
        $fileName = 'AnotherTest.php';

        self::assertNotContains(
            $fileName,
            $this->subject->findTestCaseFilesInDirectory($path)
        );
    }


    /*
     * Tests concerning isTestCaseFileName
     */

    /**
     * @test
     */
    public function isTestCaseFileNameForTestSuffixReturnsTrue()
    {
        self::assertTrue(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . 'OneTest.php'
            )
        );
    }

    /**
     * @test
     */
    public function isTestCaseFileNameForLowercaseTestSuffixReturnsTrue()
    {
        self::assertTrue(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . 'onetest.php'
            )
        );
    }

    /**
     * @test
     */
    public function isTestCaseFileNameForLowercaseTestcaseSuffixReturnsTrue()
    {
        self::assertTrue(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . 'Another_testcase.php'
            )
        );
    }

    /**
     * @test
     */
    public function isTestCaseFileNameForLowercaseNoUnderscoreTestcaseSuffixReturnsTrue()
    {
        self::assertTrue(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . 'anothertestcase.php'
            )
        );
    }

    /**
     * @test
     */
    public function isTestCaseFileNameForOtherPhpFileReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . 'SomethingDifferent.php'
            )
        );
    }

    /**
     * @test
     *
     * @see http://forge.typo3.org/issues/9094
     */
    public function isTestCaseFileNameForHiddenMacFileReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isTestCaseFileName(
                $this->fixturesPath . '._tx_tendbook_testTest.php'
            )
        );
    }


    /*
     * Tests concerning isValidTestCaseClassName
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function isValidTestCaseClassNameForEmptyStringThrowsException()
    {
        $this->subject->isValidTestCaseClassName('');
    }

    /**
     * Data provider for valid test case class names.
     *
     * @return array[]
     */
    public function validTestCaseClassNameDataProvider()
    {
        // Note: This currently does not contain any other classes as loading any other valid test case classes would
        // cause them to be listed as valid test cases in the user interface.
        $classNames = array(
            'subclass of Tx_Phpunit_TestCase' => array(get_class($this)),
        );

        return $classNames;
    }

    /**
     * @test
     *
     * @dataProvider validTestCaseClassNameDataProvider
     *
     * @param string $className
     */
    public function isValidTestCaseClassNameForValidClassNamesReturnsTrue($className)
    {
        self::assertTrue(
            $this->subject->isValidTestCaseClassName($className)
        );
    }

    /**
     * Data provider for invalid test case class names.
     *
     * @return array[]
     */
    public function invalidTestCaseClassNameDataProvider()
    {
        $this->createDummyInvalidTestCaseClasses();

        $invalidClassNames = array(
            'stdClass' => array('stdClass'),
            'inexistent class without valid suffix' => array('InexistentClassWithoutValidSuffix'),
            'inexistent class with valid Test suffix' => array('InexistentClassTest'),
            'inexistent class with valid _testcase suffix' => array('InexistentClass_testcase'),
            'existing class with valid Test suffix without valid base class' => array('SomeDummyInvalidTest'),
            'existing class with valid _testcase suffix without valid base class' => array('SomeDummyInvalid_testcase'),
            'PHPUnit extension base test class' => array('Tx_Phpunit_TestCase'),
            'PHPUnit framework base test class' => array('PHPUnit_Framework_TestCase'),
            'PHPUnit extension selenium base test class' => array('Tx_Phpunit_Selenium_TestCase'),
            'PHPUnit framework selenium base test class' => array('PHPUnit_Extensions_Selenium2TestCase'),
            'PHPUnit extension database base test class' => array('Tx_Phpunit_Database_TestCase'),
            'abstract subclass of PHPUnit extension base test class' => array('Tx_Phpunit_TestCase'),
        );

        $classNamesThatMightNotExist = array(
            'extbase selenium base test class (before 6.0)' => array('Tx_Extbase_SeleniumBaseTestCase'),
            'extbase selenium base test class (since 6.0)' => array('\\TYPO3\\CMS\\Extbase\\Tests\\SeleniumBaseTestCase'),
            'extbase base test class (before 1.3)' => array('Tx_Extbase_BaseTestCase'),
            'extbase base test class (1.3-4.7)' => array('Tx_Extbase_Tests_Unit_BaseTestCase'),
            'extbase unit base test class (since 6.0)' => array('TYPO3\\CMS\\Extbase\\Tests\\Unit\\BaseTestCase'),
            'extbase functional base test class (since 6.0)' => array('Tx_Extbase_Tests_Functional_BaseTestCase'),
            'Core base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\BaseTestCase'),
            'Core unit base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\UnitTestCase'),
            'Core functional base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\FunctionalTestCase'),
        );
        foreach ($classNamesThatMightNotExist as $key => $className) {
            if (class_exists($className[0], true)) {
                $invalidClassNames[$key] = $className;
            }
        }

        return $invalidClassNames;
    }

    /**
     * Creates some dummy invalid test case classes used for invalidTestCaseClassNameDataProvider.
     *
     * @return void
     */
    protected function createDummyInvalidTestCaseClasses()
    {
        $classNamesWithoutBaseClasses = array('SomeDummyInvalidTest', 'SomeDummyInvalid_testcase');
        foreach ($classNamesWithoutBaseClasses as $className) {
            if (!class_exists($className, false)) {
                eval('class ' . $className . ' {}');
            }
        }

        $abstractSubclassTestcaseName = 'AbstractDummyTestcase';
        if (!class_exists($abstractSubclassTestcaseName, false)) {
            eval('class ' . $abstractSubclassTestcaseName . ' extends Tx_Phpunit_TestCase {}');
        }
    }

    /**
     * @test
     *
     * @dataProvider invalidTestCaseClassNameDataProvider
     *
     * @param string $className
     */
    public function isValidTestCaseClassNameForInvalidClassNamesReturnsFalse($className)
    {
        self::assertFalse(
            $this->subject->isValidTestCaseClassName($className)
        );
    }
}
