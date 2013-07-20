<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_TestListenerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_TestListener
	 */
	private $fixture;

	/**
	 * @var Tx_Phpunit_Service_FakeOutputService
	 */
	protected $outputService = NULL;

	public function setUp() {
		$namePrettifier = new PHPUnit_Util_TestDox_NamePrettifier();
		$this->outputService = new Tx_Phpunit_Service_FakeOutputService();

		$fixtureClassName = $this->createAccessibleProxy();
		$this->fixture = new $fixtureClassName();
		$this->fixture->injectNamePrettifier($namePrettifier);
		$this->fixture->injectOutputService($this->outputService);
	}

	public function tearDown() {
		$this->fixture->__destruct();

		unset($this->fixture, $this->outputService);
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a subclass Tx_Phpunit_BackEnd_TestListener with the protected
	 * functions made public.
	 *
	 * @return string the name of the accessible proxy class
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_BackEnd_TestListenerAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_BackEnd_TestListener {' .
				'  public function createReRunLink(PHPUnit_Framework_TestCase $test) {' .
				'    return parent::createReRunLink($test);' .
				'  }' .
				'  public function createReRunUrl(PHPUnit_Framework_TestCase $test) {' .
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
	 * @return boolean TRUE if a diff tool was found, FALSE otherwise
	 */
	protected function isDiffToolAvailable() {
		$filePath = t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Backend/Fixtures/LoadMe.php';
		// Makes sure everything is sent to the stdOutput.
		$executeCommand = $GLOBALS['TYPO3_CONF_VARS']['BE']['diff_path'] . ' 2>&1 ' . $filePath . ' ' . $filePath;
		$result = array();
		t3lib_utility_Command::exec($executeCommand, $result);

		return empty($result);
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesTestListenerSubclass() {
		$className = $this->createAccessibleProxy();

		$this->assertInstanceOf(
			'Tx_Phpunit_BackEnd_TestListener',
			new $className()
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function addFailureOutputsTestName() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		/** @var $error PHPUnit_Framework_AssertionFailedError|PHPUnit_Framework_MockObject_MockObject */
		$error = $this->getMock('PHPUnit_Framework_AssertionFailedError');
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);

		$this->assertContains(
			'aTestName',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addErrorOutputsTestNameHtmlSpecialchared() {
		$testName = '<b>b</b>';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array($testName));
		$time = 0.0;

		$this->fixture->addError($testCase, new Exception(), $time);

		$this->assertContains(
			htmlspecialchars($testName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addFailureOutputsTestNameHtmlSpecialchared() {
		$testName = '<b>b</b>';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array($testName));
		/** @var $error PHPUnit_Framework_AssertionFailedError|PHPUnit_Framework_MockObject_MockObject */
		$error = $this->getMock('PHPUnit_Framework_AssertionFailedError');
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);

		$this->assertContains(
			htmlspecialchars($testName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addFailureWithComparisonFailureOutputsHtmlSpecialcharedExpectedString() {
		if (!$this->isDiffToolAvailable()) {
			$this->markTestSkipped('This test needs a working diff tool. Please see [BE][diff_path] in the install tool.');
		}

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$error = new PHPUnit_Framework_ExpectationFailedException(
			'', new PHPUnit_Framework_ComparisonFailure(
				'expected&correct', 'actual&incorrect', 'expected&correct', 'actual&incorrect'
			)
		);
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);

		$this->assertContains(
			'expected&amp;correct',
			strip_tags($this->outputService->getCollectedOutput())
		);
	}

	/**
	 * @test
	 */
	public function addFailureWithComparisonFailureForTwoStringsOutputsHtmlSpecialcharedActualString() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$error = new PHPUnit_Framework_ExpectationFailedException(
			'', new PHPUnit_Framework_ComparisonFailure(
				'expected&correct', 'actual&incorrect', 'expected&correct', 'actual&incorrect'
			)
		);
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);

		$this->assertContains(
			'actual&amp;incorrect',
			strip_tags($this->outputService->getCollectedOutput())
		);
	}

	/**
	 * @test
	 */
	public function addFailureWithComparisonFailureForTwoStringsDoesNotCrash() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$error = new PHPUnit_Framework_ExpectationFailedException(
			'', new PHPUnit_Framework_ComparisonFailure(
				'expected&correct', 'actual&incorrect', 'expected&correct', 'actual&incorrect'
			)
		);
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);
	}

	/**
	 * @test
	 */
	public function addFailureWithNullComparisonFailureDoesNotCrash() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$error = new PHPUnit_Framework_ExpectationFailedException('', NULL);
		$time = 0.0;

		$this->fixture->addFailure($testCase, $error, $time);
	}

	/**
	 * @test
	 */
	public function addIncompleteTestOutputsHtmlSpecialcharedTestName() {
		$testName = 'a<b>Test</b>Name';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject  */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array($testName));
		$exception = new Exception();
		$time = 0.0;

		$this->fixture->addIncompleteTest($testCase, $exception, $time);

		$this->assertContains(
			htmlspecialchars($testName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addIncompleteTestOutputsHtmlSpecialcharedExceptionMessage() {
		$message = 'a<b>Test</b>Name';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject  */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$exception = new Exception($message);
		$time = 0.0;

		$this->fixture->addIncompleteTest($testCase, $exception, $time);

		$this->assertContains(
			htmlspecialchars($message),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$message,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addSkippedTestOutputsSpecialcharedTestName() {
		$testName = 'a<b>Test</b>Name';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject  */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array($testName));
		$exception = new Exception();
		$time = 0.0;

		$this->fixture->addSkippedTest($testCase, $exception, $time);

		$this->assertContains(
			htmlspecialchars($testName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function addSkippedTestOutputsHtmlSpecialcharedExceptionMessage() {
		$message = 'a<b>Test</b>Name';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject  */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$exception = new Exception($message);
		$time = 0.0;

		$this->fixture->addSkippedTest($testCase, $exception, $time);

		$this->assertContains(
			htmlspecialchars($message),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$message,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function startTestSuiteOutputsPrettifiedTestClassName() {
		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('prettifyTestClass'));
		$fixture->injectOutputService($this->outputService);

		/** @var $testSuite PHPUnit_Framework_TestSuite|PHPUnit_Framework_MockObject_MockObject */
		$testSuite = $this->getMock('PHPUnit_Framework_TestSuite', array('run'), array('aTestSuiteName'));
		$fixture->expects($this->once())->method('prettifyTestClass')
			->with('aTestSuiteName')->will($this->returnValue('a test suite name'));

		$fixture->startTestSuite($testSuite);

		$this->assertContains(
			'a test suite name',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function endTestSuiteCanBeCalled() {
		/** @var $testSuite PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$testSuite = $this->getMock('PHPUnit_Framework_TestSuite');

		$this->fixture->endTestSuite($testSuite);
	}

	/**
	 * @test
	 */
	public function startTestSetsTimeLimitOf240Seconds() {
		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('setTimeLimit'));
		$fixture->injectOutputService($this->outputService);

		$fixture->expects($this->once())->method('setTimeLimit')->with(240);

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase');
		$fixture->startTest($testCase);
	}

	/**
	 * @test
	 */
	public function startTestOutputsCurrentTestNumberAndDataProviderNumberAsHtmlId() {
		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject  */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('setTimeLimit'));
		$fixture->injectOutputService($this->outputService);

		$fixture->setTestNumber(42);
		$fixture->setDataProviderNumber(91);

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject  */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase');
		$fixture->startTest($testCase);

		$this->assertContains(
			'id="testcaseNum-42_91"',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function startTestOutputsReRunLink() {
		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject  */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('setTimeLimit', 'createReRunLink'));
		$fixture->injectOutputService($this->outputService);

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase');
		$fixture->expects($this->once())->method('createReRunLink')
			->with($testCase)->will($this->returnValue('the re-run URL'));

		$fixture->startTest($testCase);

		$this->assertContains(
			'the re-run URL',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function startTestOutputsPrettifiedTestName() {
		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject  */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('setTimeLimit', 'prettifyTestMethod'));
		$fixture->injectOutputService($this->outputService);

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array('aTestName'));
		$fixture->expects($this->once())->method('prettifyTestMethod')
			->with('aTestName')->will($this->returnValue('a test name'));

		$fixture->startTest($testCase);

		$this->assertContains(
			'a test name',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function startTestOutputsPrettifiedTestNameHtmlSpecialchared() {
		$testName = '<b>b</b>';

		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject  */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('setTimeLimit', 'prettifyTestMethod'));
		$fixture->injectOutputService($this->outputService);

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('run'), array($testName));
		$fixture->expects($this->once())->method('prettifyTestMethod')->with($testName)->will($this->returnValue($testName));

		$fixture->startTest($testCase);

		$this->assertContains(
			htmlspecialchars($testName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function startTestSuiteOutputsPrettifiedTestClassNameHtmlSpecialchared() {
		$testSuiteName = '<b>b</b>';

		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject  */
		$fixture = $this->getMock('Tx_Phpunit_BackEnd_TestListener', array('prettifyTestClass'));
		$fixture->injectOutputService($this->outputService);

		/** @var $testSuite PHPUnit_Framework_TestSuite|PHPUnit_Framework_MockObject_MockObject */
		$testSuite = $this->getMock('PHPUnit_Framework_TestSuite', array('run'), array($testSuiteName));
		$fixture->expects($this->once())->method('prettifyTestClass')
			->with($testSuiteName)->will($this->returnValue($testSuiteName));

		$fixture->startTestSuite($testSuite);

		$this->assertContains(
			htmlspecialchars($testSuiteName),
			$this->outputService->getCollectedOutput()
		);
		$this->assertNotContains(
			$testSuiteName,
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function endTestAddsTestAssertionsToTotalAssertionCount() {
		/** @var $testCase1 PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase1 = $this->getMock('PHPUnit_Framework_TestCase', array('getNumAssertions'));
		$testCase1->expects($this->once())->method('getNumAssertions')->will($this->returnValue(1));

		$this->fixture->endTest($testCase1, 0.0);
		$this->assertSame(
			1,
			$this->fixture->assertionCount(),
			'The assertions of the first test case have not been counted.'
		);

		/** @var $testCase2 PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase2 = $this->getMock('PHPUnit_Framework_TestCase', array('getNumAssertions'));
		$testCase2->expects($this->once())->method('getNumAssertions')->will($this->returnValue(4));

		$this->fixture->endTest($testCase2, 0.0);
		$this->assertSame(
			5,
			$this->fixture->assertionCount(),
			'The assertions of the second test case have not been counted.'
		);
	}

	/**
	 * @test
	 */
	public function endTestForTestCaseInstanceLeavesAssertionCountUnchanged() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase');

		$this->fixture->endTest($testCase, 0.0);
		$this->assertSame(
			0,
			$this->fixture->assertionCount()
		);
	}

	/**
	 * @test
	 */
	public function endTestForPlainTestInstanceLeavesAssertionCountUnchanged() {
		/** @var $test PHPUnit_Framework_Test|PHPUnit_Framework_MockObject_MockObject */
		$test = $this->getMock('PHPUnit_Framework_Test');

		$this->fixture->endTest($test, 0.0);
		$this->assertSame(
			0,
			$this->fixture->assertionCount()
		);
	}

	/**
	 * @test
	 */
	public function endTestIncreasesTotalNumberOfDataProvidedTestsWhenRunWithDataProvidedTests() {
		/** @var $test PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$test = $this->getMock('PHPUnit_Framework_TestCase', array('dummy'), array('Test 1'));
		/** @var $test2 PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$test2 = $this->getMock('PHPUnit_Framework_TestCase', array('dummy'), array('Test 2'));

		$this->fixture->endTest($test, 0.0);
		$this->fixture->endTest($test2, 0.0);

		$this->assertSame(
			1,
			$this->fixture->getTotalNumberOfDetectedDataProviderTests()
		);
	}

	/**
	 * @test
	 */
	public function endTestDoesNotIncreaseTotalNumberOfDataProvidedTestsWhenRunWithNormalTests() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array('dummy'), array('FirstTest'));
		/** @var $testCase2 PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase2 = $this->getMock('PHPUnit_Framework_TestCase', array('dummy'), array('SecondTest'));

		$this->fixture->endTest($testCase, 0.0);
		$this->fixture->endTest($testCase2, 0.0);

		$this->assertSame(
			0,
			$this->fixture->getTotalNumberOfDetectedDataProviderTests()
		);
	}

	/**
	 * @test
	 */
	public function createReRunLinkContainsLinkToReRunUrl() {
		$reRunUrl = 'index.php?reRun=1&amp;foo=bar';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('createReRunUrl'));
		$fixture->expects($this->once())->method('createReRunUrl')
			->will($this->returnValue($reRunUrl));

		$this->assertContains(
			'<a href="' . $reRunUrl . '"',
			$fixture->createReRunLink($testCase)
		);
	}

	/**
	 * @test
	 */
	public function createReRunLinkAddsSpaceAfterLink() {
		$reRunUrl = 'index.php?reRun=1&amp;foo=bar';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('createReRunUrl'));
		$fixture->expects($this->once())->method('createReRunUrl')
			->will($this->returnValue($reRunUrl));

		$this->assertContains(
			'</a> ',
			$fixture->createReRunLink($testCase)
		);
	}

	/**
	 * @test
	 */
	public function createReRunLinkUsesEmptyAltAttribute() {
		$reRunUrl = 'index.php?reRun=1&amp;foo=bar';

		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_MockObject_MockObject */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		/** @var $fixture Tx_Phpunit_BackEnd_TestListener|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('createReRunUrl'));
		$fixture->expects($this->once())->method('createReRunUrl')
			->will($this->returnValue($reRunUrl));

		$this->assertContains(
			'alt=""',
			$fixture->createReRunLink($testCase)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsModuleParameter() {
		/** @var $testCase PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$testCase = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		$this->assertContains(
			'mod.php?M=tools_txphpunitbeM1',
			$this->fixture->createReRunUrl($testCase)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsRunSingleCommand() {
		/** @var $test PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$test = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		$this->assertContains(
			'tx_phpunit[command]=runsingletest',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsTestCaseFileName() {
		/** @var $test PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$test = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		$this->fixture->setTestSuiteName('myTestCase');

		$this->assertContains(
			'tx_phpunit[testCaseFile]=myTestCase',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsTestCaseName() {
		/** @var $test PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$test = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		$this->fixture->setTestSuiteName('myTestCase');

		$this->assertContains(
			'tx_phpunit[testname]=myTest',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlEscapesAmpersands() {
		/** @var $test PHPUnit_Framework_TestCase|PHPUnit_Framework_TestSuite */
		$test = $this->getMock('PHPUnit_Framework_TestCase', array(), array('myTest'));

		$this->fixture->setTestSuiteName('myTestCase');

		$this->assertContains(
			'&amp;',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodForTestPrefixByDefaultReturnsNameUnchanged() {
		$camelCaseName = 'testFreshEspressoTastesNice';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestMethod($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodForTestPrefixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestPrefix() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'Fresh espresso tastes nice',
			$this->fixture->prettifyTestMethod('testFreshEspressoTastesNice')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodForTestPrefixWithUnderscoreByDefaultReturnsNameUnchanged() {
		$camelCaseName = 'test_freshEspressoTastesNice';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestMethod($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodForTestPrefixWithUnderscoreAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestPrefix() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'Fresh espresso tastes nice',
			$this->fixture->prettifyTestMethod('test_freshEspressoTastesNice')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodByDefaultReturnsNameUnchanged() {
		$camelCaseName = 'freshEspressoTastesNice';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestMethod($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestMethodAfterUseHumanReadableTextFormatConvertCamelCaseToWords() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'Fresh espresso tastes nice',
			$this->fixture->prettifyTestMethod('freshEspressoTastesNice')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassByDefaultReturnsNameUnchanged() {
		$camelCaseName = 'tx_phpunit_BackEnd_TestListenerTest';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestClass($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForTestSuffixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTxPrefix() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'phpunit BackEnd TestListener',
			$this->fixture->prettifyTestClass('tx_phpunit_BackEnd_TestListenerTest')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForTestcaseSuffixAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTxPrefix() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'phpunit BackEnd TestListener',
			$this->fixture->prettifyTestClass('tx_phpunit_BackEnd_TestListener_testcase')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForExtbaseClassNameByDefaultReturnsNameUnchanged() {
		$camelCaseName = 'Tx_Phpunit_BackEnd_TestListenerTest';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestClass($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForExtbaseClassNameAfterUseHumanReadableTextFormatConvertCamelCaseToWordsAndDropsTestSuffix() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			'Phpunit BackEnd TestListener',
			$this->fixture->prettifyTestClass('Tx_Phpunit_BackEnd_TestListenerTest')
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForCoreTestByDefaultReturnsNameUnchanged() {
		$camelCaseName = 't3lib_formprotection_InstallToolFormProtectionTest';

		$this->assertSame(
			$camelCaseName,
			$this->fixture->prettifyTestClass($camelCaseName)
		);
	}

	/**
	 * @test
	 */
	public function prettifyTestClassForCoreTestAndForTestSuffixAfterUseHumanReadableTextFormatConvertCamelCaseToWords() {
		$this->fixture->useHumanReadableTextFormat();

		$this->assertSame(
			't3lib formprotection InstallToolFormProtection',
			$this->fixture->prettifyTestClass('t3lib_formprotection_InstallToolFormProtectionTest')
		);
	}

	/**
	 * @test
	 */
	public function assertionCountInitiallyReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->assertionCount()
		);
	}

	/**
	 * @test
	 */
	public function assertionCountReturnsNumberOfAssertions() {
		$this->fixture->setNumberOfAssertions(42);

		$this->assertSame(
			42,
			$this->fixture->assertionCount()
		);
	}
}
?>