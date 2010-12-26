<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2010 Robert Lemke <robert@typo3.org>
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
 * Class tx_phpunit_testlistener for the "phpunit" extension.
 *
 * This class renders the output of the single tests in the phpunit BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Robert Lemke <robert@typo3.org>
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_phpunit_testlistener implements PHPUnit_Framework_TestListener {
	protected $resultArr = array();

	/**
	 * the total number of tests to run
	 *
	 * @var integer
	 */
	protected $totalNumberOfTests = 0;

	/**
	 * the number of the current test (zero-based)
	 *
	 * @var integer
	 */
	protected $currentTestNumber = 0;

	/**
	 * the number of the current data provider within a test (zero-based)
	 *
	 * @var integer
	 */
	protected $currentDataProviderNumber = 0;

	/**
	 * the name of the current test case
	 *
	 * @var string
	 */
	private $currentTestCaseName = '';

	/**
	 * the name of the current test
	 *
	 * @var string
	 */
	private $previousTestName = '';

	private $memoryUsageStartOfTest = 0;

	private $memoryUsageEndOfTest = 0;

	public $totalLeakedMemory = 0;

	/**
	 * @var integer
	 */
	protected $testAssertions = 0;

	/**
	 * Indicate that the "testdox" format is used to display test case names
	 *
	 * @var boolean
	 */
	private $useHumanReadableTextFormat = FALSE;

	/**
	 * @var PHPUnit_Util_TestDox_NamePrettifier
	 */
	private $NamePrettifier = NULL;

	/**
	 * @var FALSE
	 */
	private $enableShowMemoryAndTime = FALSE;

	/**
	 * Initializes the test listener.
	 */
	public function __construct() {
		$this->NamePrettifier = new PHPUnit_Util_TestDox_NamePrettifier();
	}

	/**
	 * Sets the total number of tests to run (used for displaying the progress
	 * bar).
	 *
	 * @param integer $totalNumberOfTests
	 *        the total number of tests to run, must be >= 0
	 */
	public function setTotalNumberOfTests($totalNumberOfTests) {
		$this->totalNumberOfTests = $totalNumberOfTests;
	}

	/**
	 * Enable the option to show the memory leak and time usage of an test.
	 */
	public function enableShowMenoryAndTime() {
		$this->enableShowMemoryAndTime = TRUE;
	}

	/**
	 */
	public function useHumanReadableTextFormat() {
		$this->useHumanReadableTextFormat = TRUE;
	}

	/**
	 * An error occurred.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception $e
	 */
	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$fileName = str_replace(PATH_site, '', $e->getFile());
		$lineNumber = $e->getLine();

		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadError");/*]]>*/</script>
			<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseError");/*]]>*/</script>
			<strong><span class="hadError">!</span> Error</strong> in test case <em>' . $test->getName() . '</em>
			<br />File: <em>' . $fileName . '</em>
			<br />Line: <em>' . $lineNumber . '</em>' .
			'<div class="message">' . nl2br(htmlspecialchars($e->getMessage())) . '</div>';
		flush();
	}

	/**
	 * A failure occurred.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param PHPUnit_Framework_AssertionFailedError $e
	 */
	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		$testCaseTraceArr = $this->getFirstNonPHPUnitTrace($e->getTrace());
		$fileName = str_replace(PATH_site, '', $testCaseTraceArr['file']);

		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadFailure");/*]]>*/</script>
			<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseFailure");/*]]>*/</script>
			<strong><span class="hadFailure">!</span> Failure</strong> in test case <em>' . $test->getName() . '</em>
			<br />File: <em>' . $fileName . '</em>
			<br />Line: <em>' . $testCaseTraceArr['line'] . '</em>';

		if (method_exists($e, 'getDescription')) {
			$message = $e->getDescription();
		} else {
			$message = $e->getMessage();
		}
		echo '<div class="message">' . nl2br(htmlspecialchars($message)) . '</div>';

		if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
			$comparisonFailure = $e->getComparisonFailure();

			$expected = htmlspecialchars($comparisonFailure->getExpected());
			$actual = htmlspecialchars($comparisonFailure->getActual());

			$diff = t3lib_div::makeInstance('t3lib_diff');
			echo $diff->makeDiffDisplay($actual, $expected, 'pre');
		};

		flush();
	}

	/**
	 * Incomplete test.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception $e
	 */
	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadNotImplemented");/*]]>*/</script>
			<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseNotImplemented");/*]]>*/</script>
			<span class="inCompleteTest">!</span> <strong>Incomplete test</strong> <em>' . $test->getName()
			 . '</em> in file <em>' . $e->getFile() . '</em> line <em>' . $e->getLine() . '</em>:<br />
			' . $e->getMessage() . '<br />';
		flush();
	}

	/**
	 * Skipped test.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 * @param  Exception			  $e
	 * @param  float				  $time
	 */
	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadSkipped");/*]]>*/</script>
			<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseSkipped");/*]]>*/</script>
			<span class="inSkippedTest">!</span> <strong>Skipped test</strong> <em>' . $test->getName() . '</em> in file <em>'
			 . $e->getFile() . '</em> line <em>' . $e->getLine() . '</em>:<br />
			' . $e->getMessage() . '<br />';
		flush();
	}

	/**
	 * A testsuite started.
	 *
	 * @param  PHPUnit_Framework_TestSuite $suite
	 */
	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		$this->setTestSuiteName($suite->getName());
		if (($suite instanceof PHPUnit_Framework_TestSuite_DataProvider)
			|| ($suite->getName() === 'tx_phpunit_basetestsuite')
		) {
			return;
		}

		echo '<h2 class="testSuiteName">Testsuite: ' . $this->prettifyTestClass($suite->getName()) . '</h2>';
		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("wasSuccessful");/*]]>*/</script>';
	}

	/**
	 * Sets the name of the test suite that is used for creating the re-run
	 * link.
	 *
	 * @param string $name the name of the test suite, must not be empty
	 */
	public function setTestSuiteName($name) {
		$this->currentTestCaseName = $name;
	}

	/**
	 * A testsuite ended.
	 *
	 * @param  PHPUnit_Framework_TestSuite $suite
	 */
	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
	}

	/**
	 * A test started.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 */
	public function startTest(PHPUnit_Framework_Test $test) {
		// A single test has to take less than this or else PHP will time out.
		set_time_limit(30);
		echo '<div id="testcaseNum-' . $this->currentTestNumber . '_' . $this->currentDataProviderNumber .
			'" class="testcaseOutput testcaseSuccess">';

		echo '<h3>' . $this->createReRunLink($test) . $this->prettifyTestMethod($test->getName()) . '</h3><div>';
		$this->memoryUsageStartOfTest = memory_get_usage();
	}

	/**
	 * A test ended.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 */
	public function endTest(PHPUnit_Framework_Test $test, $time) {
		$this->memoryUsageEndOfTest = memory_get_usage();

		// Tests with the same name are a sign of data provider usage.
		$testNameParts = explode(' ', $test->getName());
		$testName = get_class($test) . ':' . $testNameParts[0];
		if ($testName !== $this->previousTestName) {
			$this->currentDataProviderNumber = 0;
			$this->currentTestNumber++;
			$this->previousTestName = $testName;
		} else {
			$this->currentDataProviderNumber++;
		}

		$percentDone = intval(($this->currentTestNumber / $this->totalNumberOfTests) * 100);
		$leakedMemory = ($this->memoryUsageEndOfTest - $this->memoryUsageStartOfTest);
		$this->totalLeakedMemory += $leakedMemory;

		if ($test instanceof PHPUnit_Framework_TestCase) {
			$this->testAssertions += $test->getNumAssertions();
		}

		echo '</div>';
		if ($this->enableShowMemoryAndTime === TRUE) {
			echo '<span class="memory-leak small-font"><strong>Memory leak:</strong> ' .
				t3lib_div::formatSize($leakedMemory) . 'B </span>';
			echo '<span class="time-usages small-font"><strong>Time:</strong> ' . sprintf('%.4f', $time) .
				' sec.</span><br />';
		}
		echo '</div>';
		echo '<script type="text/javascript">/*<![CDATA[*/document.getElementById("progress-bar").style.width = "' .
			$percentDone . '%";/*]]>*/</script>';
		echo '<script type="text/javascript">/*<![CDATA[*/document.getElementById("transparent-bar").style.width = "' .
			(100 - $percentDone) . '%";/*]]>*/</script>';
		flush();
	}

	/**
	 * Returns the first trace information which is not caused by the PHPUnit file
	 * "Framework/Assert.php".
	 *
	 * @param array $traceArr the trace array
	 * @return array trace information
	 */
	protected function getFirstNonPHPUnitTrace(array $traceArr) {
		$testCaseTraceArr = array();
		foreach ($traceArr as $singleTraceArr) {
			if (!stristr($singleTraceArr['file'], 'Framework/Assert.php')) {
				$testCaseTraceArr = $singleTraceArr;
				break;
			}
		}
		return $testCaseTraceArr;
	}

	/**
	 * Creates the link (including an icon) to re-run a certain test.
	 *
	 * @param PHPUnit_Framework_TestSuite $test
	 * the test for which to create the re-run link
	 *
	 * @return string the link to re-run the given test, will not be empty
	 */
	private function createReRunLink(PHPUnit_Framework_TestCase $test) {
		$iconImageTag = '<img style="vertical-align: middle; border: 1px solid #fff;" src="' .
			t3lib_extMgm::extRelPath('phpunit') . 'mod1/runner.gif" alt="Run this test only" />';
		return '<a href="' . $this->createReRunUrl($test) . '" title="Run this test only">' . $iconImageTag . '</a>';
	}

	/**
	 * Creates the URL to re-run a certain test.
	 *
	 * @param PHPUnit_Framework_TestSuite $test
	 *        the test for which to create the re-run URL
	 *
	 * @return string the URL to re-run the given test, will not be empty
	 */
	private function createReRunUrl(PHPUnit_Framework_TestCase $test) {
		$options = array(
			'M=tools_txphpunitbeM1',
			'command=runsingletest',
			'testCaseFile=' . $this->getTestCaseName(),
			'testname=' . $this->createTestId($test),
		);

		return htmlspecialchars('mod.php?' . implode('&', $options));
	}

	/**
	 * Creates a unique string ID for $test that can be used in URLs.
	 *
	 * @param PHPUnit_Framework_TestCase $test a test for which to create an ID
	 *
	 * @return string a unique ID for $test, not htmlspecialchared or URL-encoded yet
	 */
	protected function createTestId(PHPUnit_Framework_TestCase $test) {
		$testNameParts = explode(' ', $test->getName());

		// This is quite a hack.
		// @see http://forge.typo3.org/issues/11735
		if (strpos($this->currentTestCaseName, '::') !== FALSE) {
			$result = $testNameParts[0] . '(' . $this->getTestCaseName() . ')';
		} else {
			$result = $this->getTestCaseName() . '::' . $testNameParts[0];
		}

		return $result;
	}

	/**
	 * Gets the current test case name.
	 *
	 * @return string the current test case name, will not be empty
	 */
	protected function getTestCaseName() {
		$testCaseNameParts = explode('::', $this->currentTestCaseName);

		return $testCaseNameParts[0];
	}

	/**
	 * Prettifies the name of a test method.
	 *
	 * @param string $testName
	 * @return string
	 */
	protected function prettifyTestMethod($testName) {
		$content = '';

		if ($this->useHumanReadableTextFormat) {
			$this->NamePrettifier->setPrefix('test');
			$this->NamePrettifier->setSuffix(NULL);
			// this is required because the "setPrefix" work not very well with the prefix "test_"
			$content = $this->NamePrettifier->prettifyTestMethod(str_replace('test_', '', $testName));
		} else {
			$content = $testName;
		}

		return $content;
	}

	/**
	 * Prettifies the name of a test class.
	 *
	 * @param string $testClass
	 *
	 * @return string
	 */
	protected function prettifyTestClass($testClass) {
		$content = '';

		if ($this->useHumanReadableTextFormat) {
			$this->NamePrettifier->setPrefix('tx');
			$this->NamePrettifier->setSuffix('testcase');
			$content = $this->NamePrettifier->prettifyTestClass(str_replace('_', ' ', $testClass));
		} else {
			$content = $testClass;
		}

		return $content;
	}

	/**
	 * Retrieves the collected amount of processed assertions.
	 *
	 * @return integer
	 */
	public function assertionCount() {
		return $this->testAssertions;
	}
}
?>