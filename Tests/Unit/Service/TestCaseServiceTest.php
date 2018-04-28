<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Tests\BaseTestCase;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;
use TYPO3\CMS\Core\Tests\UnitTestCase;
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
            SingletonInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryForEmptyPathThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findTestCaseFilesInDirectory('');
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryForInexistentPathThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findTestCaseFilesInDirectory(
            $this->fixturesPath . 'DoesNotExist/'
        );
    }

    /**
     * @test
     */
    public function findTestCaseFilesInDirectoryForEmptyDirectoryReturnsEmptyArray()
    {
        vfsStream::setup('root/');
        $emptyDirectoryUrl = vfsStream::url('root/');

        self::assertSame(
            [],
            $this->subject->findTestCaseFilesInDirectory($emptyDirectoryUrl)
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
            \Tx_Phpunit_Service_TestCaseService::class,
            ['isNotFixturesPath', 'isTestCaseFileName']
        );
        $subject->expects(self::any())->method('isNotFixturesPath')->will(self::returnValue(true));
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
            \Tx_Phpunit_Service_TestCaseService::class,
            ['isNotFixturesPath', 'isTestCaseFileName']
        );
        $subject->expects(self::any())->method('isNotFixturesPath')->will(self::returnValue(true));
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

        self::assertNotEmpty(
            $result
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

        self::assertNotEmpty(
            $result
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
     */
    public function isValidTestCaseClassNameForEmptyStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

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
        $classNames = [
            'subclass of \\Tx_Phpunit_TestCase' => [get_class($this)],
        ];

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

        $invalidClassNames = [
            'stdClass' => ['stdClass'],
            'inexistent class without valid suffix' => ['InexistentClassWithoutValidSuffix'],
            'inexistent class with valid Test suffix' => ['InexistentClassTest'],
            'inexistent class with valid _testcase suffix' => ['InexistentClass_testcase'],
            'existing class with valid Test suffix without valid base class' => ['SomeDummyInvalidTest'],
            'existing class with valid _testcase suffix without valid base class' => ['SomeDummyInvalid_testcase'],
            'PHPUnit extension base test class' => ['Tx_Phpunit_TestCase'],
            'PHPUnit framework base test class' => ['PHPUnit_Framework_TestCase'],
            'PHPUnit extension selenium base test class' => [\Tx_Phpunit_Selenium_TestCase::class],
            'PHPUnit framework selenium base test class' => [\PHPUnit_Extensions_Selenium2TestCase::class],
            'PHPUnit extension database base test class' => [\Tx_Phpunit_Database_TestCase::class],
            'abstract subclass of PHPUnit extension base test class' => [\Tx_Phpunit_TestCase::class],
        ];

        $classNamesThatMightNotExist = [
            'Core base test class' => [BaseTestCase::class],
            'Core unit base test class' => [UnitTestCase::class],
            'Core functional base test class' => [FunctionalTestCase::class],
        ];
        foreach ($classNamesThatMightNotExist as $key => $className) {
            if (class_exists($className[0])) {
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
        $classNamesWithoutBaseClasses = ['SomeDummyInvalidTest', 'SomeDummyInvalid_testcase'];
        foreach ($classNamesWithoutBaseClasses as $className) {
            if (!class_exists($className, false)) {
                eval('class ' . $className . ' {}');
            }
        }

        $abstractSubclassTestcaseName = 'AbstractDummyTestcase';
        if (!class_exists($abstractSubclassTestcaseName, false)) {
            eval('class ' . $abstractSubclassTestcaseName . ' extends \\Tx_Phpunit_TestCase {}');
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
