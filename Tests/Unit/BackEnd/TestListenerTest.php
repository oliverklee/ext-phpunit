<?php

namespace OliverKlee\PhpUnit\Tests\Unit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestListenerTest extends TestCase
{
    /**
     * @var \Tx_Phpunit_BackEnd_TestListener
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_Service_FakeOutputService
     */
    protected $outputService = null;

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $namePrettifier = new \PHPUnit_Util_TestDox_NamePrettifier();
        $this->outputService = new \Tx_Phpunit_Service_FakeOutputService();

        $subjectClassName = $this->createAccessibleProxy();
        $this->subject = new $subjectClassName();
        $this->subject->injectNamePrettifier($namePrettifier);
        $this->subject->injectOutputService($this->outputService);
    }

    /*
     * Utility functions
     */

    /**
     * Creates a subclass \Tx_Phpunit_BackEnd_TestListener with the protected
     * functions made public.
     *
     * @return string the name of the accessible proxy class
     */
    private function createAccessibleProxy()
    {
        $className = 'Tx_Phpunit_BackEnd_TestListenerAccessibleProxy';
        if (!class_exists($className, false)) {
            eval(
                'class ' . $className . ' extends \\Tx_Phpunit_BackEnd_TestListener {' .
                '  public function createReRunLink(\\PHPUnit_Framework_TestCase $test) {' .
                '    return parent::createReRunLink($test);' .
                '  }' .
                '  public function createReRunUrl(\\PHPUnit_Framework_TestCase $test) {' .
                '    return parent::createReRunUrl($test);' .
                '  }' .
                '  public function prettifyTestMethod($testClass) {' .
                '    return parent::prettifyTestMethod($testClass);' .
                '  }' .
                '  public function prettifyTestClass($testClassName) {' .
                '    return parent::prettifyTestClass($testClassName);' .
                '  }' .
                '  public function setNumberOfAssertions($number) {' .
                '    $this->testAssertions = $number;' .
                '  }' .
                '  public function setTestNumber($number) {' .
                '    $this->currentTestNumber = $number;' .
                '  }' .
                '  public function setDataProviderNumber($number) {' .
                '    $this->currentDataProviderNumber = $number;' .
                '  }' .
                '}'
            );
        }

        return $className;
    }

    /**
     * Helper function to check for a working diff tool on a system.
     *
     * Tests same file to be sure there is not any error message.
     *
     * @return bool TRUE if a diff tool was found, FALSE otherwise
     */
    protected function isDiffToolAvailable()
    {
        if (empty($GLOBALS['TYPO3_CONF_VARS']['BE']['diff_path'])) {
            return false;
        }

        $filePath = ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/LoadMe.php';
        // Makes sure everything is sent to the stdOutput.
        $executeCommand = $GLOBALS['TYPO3_CONF_VARS']['BE']['diff_path'] . ' 2>&1 ' . $filePath . ' ' . $filePath;
        $result = [];
        CommandUtility::exec($executeCommand, $result);

        return empty($result);
    }

    /**
     * @test
     */
    public function createAccessibleProxyCreatesTestListenerSubclass()
    {
        $className = $this->createAccessibleProxy();

        self::assertInstanceOf(
            \Tx_Phpunit_BackEnd_TestListener::class,
            new $className()
        );
    }

    /*
     * Unit tests
     */

    /**
     * @test
     */
    public function addFailureOutputsTestName()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        /** @var \PHPUnit_Framework_AssertionFailedError|\PHPUnit_Framework_MockObject_MockObject $error */
        $error = $this->createMock(\PHPUnit_Framework_AssertionFailedError::class);
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);

        self::assertContains(
            'aTestName',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addErrorOutputsTestNameHtmlSpecialchared()
    {
        $testName = '<b>b</b>';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs([$testName])->getMock();
        $time = 0.0;

        $this->subject->addError($testCase, new \Exception(), $time);

        self::assertContains(
            htmlspecialchars($testName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addFailureOutputsTestNameHtmlSpecialchared()
    {
        $testName = '<b>b</b>';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs([$testName])->getMock();
        /** @var \PHPUnit_Framework_AssertionFailedError|\PHPUnit_Framework_MockObject_MockObject $error */
        $error = $this->createMock(\PHPUnit_Framework_AssertionFailedError::class);
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);

        self::assertContains(
            htmlspecialchars($testName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addFailureWithComparisonFailureOutputsHtmlSpecialcharedExpectedString()
    {
        if (!$this->isDiffToolAvailable()) {
            self::markTestSkipped('This test needs a working diff tool. Please see [BE][diff_path] in the install tool.');
        }

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $error = new \PHPUnit_Framework_ExpectationFailedException(
            '',
            new ComparisonFailure(
                'expected&correct',
                'actual&incorrect',
                'expected&correct',
                'actual&incorrect'
            )
        );
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);

        self::assertContains(
            'expected&amp;correct',
            strip_tags($this->outputService->getCollectedOutput())
        );
    }

    /**
     * @test
     */
    public function addFailureWithComparisonFailureForTwoStringsOutputsHtmlSpecialcharedActualString()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $error = new \PHPUnit_Framework_ExpectationFailedException(
            '',
            new ComparisonFailure(
                'expected&correct',
                'actual&incorrect',
                'expected&correct',
                'actual&incorrect'
            )
        );
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);

        self::assertContains(
            '&amp;incorrect',
            strip_tags($this->outputService->getCollectedOutput())
        );
    }

    /**
     * @test
     */
    public function addFailureWithComparisonFailureForTwoStringsDoesNotCrash()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $error = new \PHPUnit_Framework_ExpectationFailedException(
            '',
            new ComparisonFailure(
                'expected&correct',
                'actual&incorrect',
                'expected&correct',
                'actual&incorrect'
            )
        );
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);
    }

    /**
     * @test
     */
    public function addFailureWithNullComparisonFailureDoesNotCrash()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $error = new \PHPUnit_Framework_ExpectationFailedException('', null);
        $time = 0.0;

        $this->subject->addFailure($testCase, $error, $time);
    }

    /**
     * @test
     */
    public function addIncompleteTestOutputsHtmlSpecialcharedTestName()
    {
        $testName = 'a<b>Test</b>Name';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs([$testName])->getMock();
        $exception = new \Exception();
        $time = 0.0;

        $this->subject->addIncompleteTest($testCase, $exception, $time);

        self::assertContains(
            htmlspecialchars($testName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addIncompleteTestOutputsHtmlSpecialcharedExceptionMessage()
    {
        $message = 'a<b>Test</b>Name';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $exception = new \Exception($message);
        $time = 0.0;

        $this->subject->addIncompleteTest($testCase, $exception, $time);

        self::assertContains(
            htmlspecialchars($message),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $message,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addSkippedTestOutputsSpecialcharedTestName()
    {
        $testName = 'a<b>Test</b>Name';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs([$testName])->getMock();
        $exception = new \Exception();
        $time = 0.0;

        $this->subject->addSkippedTest($testCase, $exception, $time);

        self::assertContains(
            htmlspecialchars($testName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function addSkippedTestOutputsHtmlSpecialcharedExceptionMessage()
    {
        $message = 'a<b>Test</b>Name';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $exception = new \Exception($message);
        $time = 0.0;

        $this->subject->addSkippedTest($testCase, $exception, $time);

        self::assertContains(
            htmlspecialchars($message),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $message,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function startTestSuiteOutputsPrettifiedTestClassName()
    {
        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['prettifyTestClass'])->getMock();
        $subject->injectOutputService($this->outputService);

        /** @var \PHPUnit_Framework_TestSuite|\PHPUnit_Framework_MockObject_MockObject $testSuite */
        $testSuite = $this->getMockBuilder(\PHPUnit_Framework_TestSuite::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestSuiteName'])->getMock();
        $subject->expects(self::once())->method('prettifyTestClass')
            ->with('aTestSuiteName')->willReturn('a test suite name');

        $subject->startTestSuite($testSuite);

        self::assertContains(
            'a test suite name',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function endTestSuiteCanBeCalled()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $testSuite */
        $testSuite = $this->createMock(\PHPUnit_Framework_TestSuite::class);

        $this->subject->endTestSuite($testSuite);
    }

    /**
     * @test
     */
    public function startTestSetsTimeLimitOf240Seconds()
    {
        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['setTimeLimit'])->getMock();
        $subject->injectOutputService($this->outputService);

        $subject->expects(self::once())->method('setTimeLimit')->with(240);

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->createMock(\PHPUnit_Framework_TestCase::class);
        $subject->startTest($testCase);
    }

    /**
     * @test
     */
    public function startTestOutputsCurrentTestNumberAndDataProviderNumberAsHtmlId()
    {
        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['setTimeLimit'])->getMock();
        $subject->injectOutputService($this->outputService);

        $subject->setTestNumber(42);
        $subject->setDataProviderNumber(91);

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->createMock(\PHPUnit_Framework_TestCase::class);
        $subject->startTest($testCase);

        self::assertContains(
            'id="testcaseNum-42_91"',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function startTestOutputsReRunLink()
    {
        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['setTimeLimit', 'createReRunLink'])->getMock();
        $subject->injectOutputService($this->outputService);

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->createMock(\PHPUnit_Framework_TestCase::class);
        $subject->expects(self::once())->method('createReRunLink')
            ->with($testCase)->willReturn('the re-run URL');

        $subject->startTest($testCase);

        self::assertContains(
            'the re-run URL',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function startTestOutputsPrettifiedTestName()
    {
        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['setTimeLimit', 'prettifyTestMethod'])->getMock();
        $subject->injectOutputService($this->outputService);

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs(['aTestName'])->getMock();
        $subject->expects(self::once())->method('prettifyTestMethod')
            ->with('aTestName')->willReturn('a test name');

        $subject->startTest($testCase);

        self::assertContains(
            'a test name',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function startTestOutputsPrettifiedTestNameHtmlSpecialchared()
    {
        $testName = '<b>b</b>';

        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['setTimeLimit', 'prettifyTestMethod'])->getMock();
        $subject->injectOutputService($this->outputService);

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['run'])->setConstructorArgs([$testName])->getMock();
        $subject->expects(self::once())
            ->method('prettifyTestMethod')
            ->with($testName)
            ->willReturn($testName);

        $subject->startTest($testCase);

        self::assertContains(
            htmlspecialchars($testName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function startTestSuiteOutputsPrettifiedTestClassNameHtmlSpecialchared()
    {
        $testSuiteName = '<b>b</b>';

        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_TestListener::class)
            ->setMethods(['prettifyTestClass'])->getMock();
        $subject->injectOutputService($this->outputService);

        /** @var \PHPUnit_Framework_TestSuite|\PHPUnit_Framework_MockObject_MockObject $testSuite */
        $testSuite = $this->getMockBuilder(\PHPUnit_Framework_TestSuite::class)
            ->setMethods(['run'])->setConstructorArgs([$testSuiteName])->getMock();
        $subject->expects(self::once())->method('prettifyTestClass')
            ->with($testSuiteName)->willReturn($testSuiteName);

        $subject->startTestSuite($testSuite);

        self::assertContains(
            htmlspecialchars($testSuiteName),
            $this->outputService->getCollectedOutput()
        );
        self::assertNotContains(
            $testSuiteName,
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function endTestAddsTestAssertionsToTotalAssertionCount()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase1 */
        $testCase1 = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['getNumAssertions'])->getMock();
        $testCase1->expects(self::once())->method('getNumAssertions')->willReturn(1);

        $this->subject->endTest($testCase1, 0.0);
        self::assertSame(
            1,
            $this->subject->assertionCount(),
            'The assertions of the first test case have not been counted.'
        );

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase2 */
        $testCase2 = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['getNumAssertions'])->getMock();
        $testCase2->expects(self::once())->method('getNumAssertions')->willReturn(4);

        $this->subject->endTest($testCase2, 0.0);
        self::assertSame(
            5,
            $this->subject->assertionCount(),
            'The assertions of the second test case have not been counted.'
        );
    }

    /**
     * @test
     */
    public function endTestForTestCaseInstanceLeavesAssertionCountUnchanged()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->createMock(\PHPUnit_Framework_TestCase::class);

        $this->subject->endTest($testCase, 0.0);
        self::assertSame(
            0,
            $this->subject->assertionCount()
        );
    }

    /**
     * @test
     */
    public function endTestForPlainTestInstanceLeavesAssertionCountUnchanged()
    {
        /** @var \PHPUnit_Framework_Test|\PHPUnit_Framework_MockObject_MockObject $test */
        $test = $this->createMock(\PHPUnit_Framework_Test::class);

        $this->subject->endTest($test, 0.0);
        self::assertSame(
            0,
            $this->subject->assertionCount()
        );
    }

    /**
     * @test
     */
    public function endTestIncreasesTotalNumberOfDataProvidedTestsWhenRunWithDataProvidedTests()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $test */
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setMethods(['dummy'])
            ->setConstructorArgs(['Test 1'])->getMock();
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $test2 */
        $test2 = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setMethods(['dummy'])
            ->setConstructorArgs(['Test 2'])->getMock();

        $this->subject->endTest($test, 0.0);
        $this->subject->endTest($test2, 0.0);

        self::assertSame(
            1,
            $this->subject->getTotalNumberOfDetectedDataProviderTests()
        );
    }

    /**
     * @test
     */
    public function endTestDoesNotIncreaseTotalNumberOfDataProvidedTestsWhenRunWithNormalTests()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['dummy'])->setConstructorArgs(['FirstTest'])->getMock();
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase2 */
        $testCase2 = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->setMethods(['dummy'])->setConstructorArgs(['SecondTest'])->getMock();

        $this->subject->endTest($testCase, 0.0);
        $this->subject->endTest($testCase2, 0.0);

        self::assertSame(
            0,
            $this->subject->getTotalNumberOfDetectedDataProviderTests()
        );
    }

    /**
     * @test
     */
    public function createReRunLinkContainsLinkToReRunUrl()
    {
        $reRunUrl = 'index.php?reRun=1&amp;foo=bar';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])
            ->getMock();

        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder($this->createAccessibleProxy())->setMethods(['createReRunUrl'])->getMock();
        $subject->expects(self::once())->method('createReRunUrl')
            ->willReturn($reRunUrl);

        self::assertContains(
            '<a href="' . $reRunUrl . '"',
            $subject->createReRunLink($testCase)
        );
    }

    /**
     * @test
     */
    public function createReRunLinkAddsSpaceAfterLink()
    {
        $reRunUrl = 'index.php?reRun=1&amp;foo=bar';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])
            ->getMock();

        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder($this->createAccessibleProxy())->setMethods(['createReRunUrl'])->getMock();
        $subject->expects(self::once())->method('createReRunUrl')
            ->willReturn($reRunUrl);

        self::assertContains(
            '</a> ',
            $subject->createReRunLink($testCase)
        );
    }

    /**
     * @test
     */
    public function createReRunLinkUsesEmptyAltAttribute()
    {
        $reRunUrl = 'index.php?reRun=1&amp;foo=bar';

        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_MockObject_MockObject $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])
            ->getMock();

        /** @var \Tx_Phpunit_BackEnd_TestListener|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder($this->createAccessibleProxy())->setMethods(['createReRunUrl'])->getMock();
        $subject->expects(self::once())->method('createReRunUrl')
            ->willReturn($reRunUrl);

        self::assertContains(
            'alt=""',
            $subject->createReRunLink($testCase)
        );
    }

    /**
     * @test
     */
    public function createReRunUrlContainsModuleParameter()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $testCase */
        $testCase = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])
            ->getMock();

        self::assertContains(
            '.php?M=' . \Tx_Phpunit_BackEnd_Module::MODULE_NAME,
            $this->subject->createReRunUrl($testCase)
        );
    }

    /**
     * @test
     */
    public function createReRunUrlContainsRunSingleCommand()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $test */
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])->getMock();

        self::assertContains(
            'tx_phpunit%5Bcommand%5D=runsingletest',
            $this->subject->createReRunUrl($test)
        );
    }

    /**
     * @test
     */
    public function createReRunUrlContainsTestCaseFileName()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $test */
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])->getMock();

        $this->subject->setTestSuiteName('myTestCase');

        self::assertContains(
            'tx_phpunit%5BtestCaseFile%5D=myTestCase',
            $this->subject->createReRunUrl($test)
        );
    }

    /**
     * @test
     */
    public function createReRunUrlContainsTestCaseName()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $test */
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])->getMock();

        $this->subject->setTestSuiteName('myTestCase');

        self::assertContains(
            'tx_phpunit%5Btestname%5D=myTest',
            $this->subject->createReRunUrl($test)
        );
    }

    /**
     * @test
     */
    public function createReRunUrlEscapesAmpersands()
    {
        /** @var \PHPUnit_Framework_TestCase|\PHPUnit_Framework_TestSuite $test */
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)->setConstructorArgs(['myTest'])->getMock();

        $this->subject->setTestSuiteName('myTestCase');

        self::assertContains(
            '&amp;',
            $this->subject->createReRunUrl($test)
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodForTestPrefixByDefaultReturnsNameUnchanged()
    {
        $camelCaseName = 'testFreshEspressoTastesNice';

        self::assertSame(
            $camelCaseName,
            $this->subject->prettifyTestMethod($camelCaseName)
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodForTestPrefixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestPrefix(
    ) {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'Fresh espresso tastes nice',
            $this->subject->prettifyTestMethod('testFreshEspressoTastesNice')
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodForTestPrefixWithUnderscoreByDefaultReturnsNameUnchanged()
    {
        $camelCaseName = 'test_freshEspressoTastesNice';

        self::assertSame(
            $camelCaseName,
            $this->subject->prettifyTestMethod($camelCaseName)
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodForTestPrefixWithUnderscoreAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestPrefix(
    ) {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'Fresh espresso tastes nice',
            $this->subject->prettifyTestMethod('test_freshEspressoTastesNice')
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodByDefaultReturnsNameUnchanged()
    {
        $camelCaseName = 'freshEspressoTastesNice';

        self::assertSame(
            $camelCaseName,
            $this->subject->prettifyTestMethod($camelCaseName)
        );
    }

    /**
     * @test
     */
    public function prettifyTestMethodAfterUseHumanReadableTextFormatConvertCamelCaseToWords()
    {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'Fresh espresso tastes nice',
            $this->subject->prettifyTestMethod('freshEspressoTastesNice')
        );
    }

    /**
     * @test
     */
    public function prettifyTestClassByDefaultReturnsNameUnchanged()
    {
        $camelCaseName = 'tx_phpunit_BackEnd_TestListenerTest';

        self::assertSame(
            $camelCaseName,
            $this->subject->prettifyTestClass($camelCaseName)
        );
    }

    /**
     * @test
     */
    public function prettifyTestClassForTestSuffixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTxPrefix(
    ) {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'phpunit BackEnd TestListener',
            $this->subject->prettifyTestClass('tx_phpunit_BackEnd_TestListenerTest')
        );
    }

    /**
     * @test
     */
    public function prettifyTestClassForTestcaseSuffixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTxPrefix(
    ) {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'phpunit BackEnd TestListener',
            $this->subject->prettifyTestClass('tx_phpunit_BackEnd_TestListener_testcase')
        );
    }

    /**
     * @test
     */
    public function prettifyTestClassForExtbaseClassNameByDefaultReturnsNameUnchanged()
    {
        $camelCaseName = 'Tx_Phpunit_BackEnd_TestListenerTest';

        self::assertSame(
            $camelCaseName,
            $this->subject->prettifyTestClass($camelCaseName)
        );
    }

    /**
     * @test
     */
    public function prettifyTestClassForExtbaseClassNameAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestSuffix(
    ) {
        $this->subject->useHumanReadableTextFormat();

        self::assertSame(
            'Phpunit BackEnd TestListener',
            $this->subject->prettifyTestClass('Tx_Phpunit_BackEnd_TestListenerTest')
        );
    }

    /**
     * @test
     */
    public function assertionCountInitiallyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->assertionCount()
        );
    }

    /**
     * @test
     */
    public function assertionCountReturnsNumberOfAssertions()
    {
        $this->subject->setNumberOfAssertions(42);

        self::assertSame(
            42,
            $this->subject->assertionCount()
        );
    }
}
