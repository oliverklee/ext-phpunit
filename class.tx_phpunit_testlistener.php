<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004 Robert Lemke <robert@typo3.org>
 *  (c) 2008 Kasper Ligaard <kasperligaard@gmail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/TestDox/NamePrettifier.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

class tx_phpunit_testlistener implements PHPUnit_Framework_TestListener {

	protected $resultArr = array();
	/**
	 * the total number of tests to run
	 *
	 * @var integer
	 */
	protected $totalNumberOfTests = 0;
	protected $currentTestNumber = 0;
 	private $currentTestCaseName;
 	private $memoryUsageStartOfTest = 0;
 	private $memoryUsageEndOfTest = 0;
 	public $totalLeakedMemory = 0;

	/**
	 * @var    integer
	 */
	protected $testAssertions = 0;

 	/**
	 * Indicate that the "testdox" format is used to display test case names
	 *
	 * @var boolean
	 */
	private $useHumanReadableTextFormat = false;

	/**
	 * @var PHPUnit_Util_TestDox_NamePrettifier
	 */
	private $NamePrettifier = null;

	/**
 	 * @var boolean  whether the experimental progress bar should be used
 	 */
 	private $useExperimentalProgressBar = false;

 	/**
 	 * @var false
 	 */
 	private $enableShowMemoryAndTime = false;

	/**
	 * Init the testlistener
	 *
	 * @return void
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function __construct() {
		$this->NamePrettifier = new PHPUnit_Util_TestDox_NamePrettifier;
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
	 *
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function enableShowMenoryAndTime() {
		$this->enableShowMemoryAndTime = true;
	}

	/**
	 * @return void
     * @author Michael Klapper <michael.klapper@aoemedia.de>
     */
    public function useHumanReadableTextFormat() {
        $this->useHumanReadableTextFormat = true;
    }

	/**
	 * Enables the experimental progress bar.
	 */
	public function enableExperimentalProgressBar() {
		$this->useExperimentalProgressBar = true;
	}

	/**
	 *	An error occurred.
	 *
	 *	@param  PHPUnit_Framework_Test $test
	 *	@param  Exception               $e
	 */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	$testCaseTraceArr = $this->getFirstNonPHPUnitTrace($e->getTrace());
		$fileName = str_replace(PATH_site, '', $testCaseTraceArr['file']);

		if ($this->useExperimentalProgressBar) {
			echo '<script type="text/javascript">setClass("tx_phpunit_testcase_nr_' . $this->currentTestNumber . '", "hadError");</script>';
    	}

		echo '
			<script type="text/javascript">setClass("progress-bar","hadError");</script>
			<script type="text/javascript">setClass("testcaseNum-'.$this->currentTestNumber.'","testcaseError");</script>
			<strong><span class="hadError">!</span> Error</strong> in test case <em>'.$test->getName().'</em>
			<br />in file<em> '.$fileName.'</em>
			<br />on line<em> '.$testCaseTraceArr['line'].'</em>:
			<div class="message">Message:<br>'.htmlspecialchars($e->getMessage()).'</div>';
    	flush();
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
    	$testCaseTraceArr = $this->getFirstNonPHPUnitTrace($e->getTrace());
		$fileName = str_replace(PATH_site, '', $testCaseTraceArr['file']);

		if ($this->useExperimentalProgressBar) {
			echo '<script type="text/javascript">setClass("tx_phpunit_testcase_nr_' . $this->currentTestNumber . '", "hadFailure");</script>';
    	}

    	echo '
			<script type="text/javascript">setClass("progress-bar","hadFailure");</script>
			<script type="text/javascript">setClass("testcaseNum-'.$this->currentTestNumber.'","testcaseFailure");</script>
			<strong><span class="hadFailure">!</span> Failure</strong> in test case <em>'.$test->getName().'</em>
			<br />File: <em>'.$fileName.'</em>
			<br />Line: <em>'.$testCaseTraceArr['line'].'</em>:
			<br /><strong>Description</strong>
			';

		if (method_exists($e, 'getDescription')) {
			echo '<div class="message">'.htmlspecialchars($e->getDescription()).'</div>';
		} else {
			echo '<div class="message">'.htmlspecialchars($e->getMessage()).'</div>';
		}

		flush();
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception               $e
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	echo '
    		<script type="text/javascript">setClass("progress-bar","hadNotImplemented");</script>
    		<script type="text/javascript">setClass("testcaseNum-'.$this->currentTestNumber.'","testcaseNotImplemented");</script>
			<span class="inCompleteTest">!</span> <strong>Incomplete test</strong> <em>'.$test->getName().'</em> in file <em>'.$e->getFile().'</em> line <em>'.$e->getLine().'</em>:<br />
			'.$e->getMessage().'<br />
		';
		flush();
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	echo '
    		<script type="text/javascript">setClass("progress-bar","hadSkipped");</script>
    		<script type="text/javascript">setClass("testcaseNum-'.$this->currentTestNumber.'","testcaseSkipped");</script>
			<span class="inSkippedTest">!</span> <strong>Skipped test</strong> <em>'.$test->getName().'</em> in file <em>'.$e->getFile().'</em> line <em>'.$e->getLine().'</em>:<br />
			'.$e->getMessage().'<br />
		';
		flush();
    }

    /**
    * A testsuite started.
    *
    * @param  PHPUnit_Framework_TestSuite $suite
    */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
    	$this->currentTestCaseName = $suite->getName();

    	if (! $suite instanceOf PHPUnit_Framework_TestSuite_DataProvider && $suite->getName() !== 'tx_phpunit_basetestsuite') {
			echo '<h2 class="testSuiteName">Testsuite: ' . $this->prettifyTestClass($suite->getName()) . '</h2>';
    	}
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
    	set_time_limit(30); // A single test has to take less than this or else PHP will timeout.
		echo '<div id="testcaseNum-'.$this->currentTestNumber.'" class="testcaseOutput testcaseSuccess">';

		if ($this->useExperimentalProgressBar) {
			echo '<script type="text/javascript">setClass("tx_phpunit_testcase_nr_' . $this->currentTestNumber . '", "wasSuccessful");</script>';
    	}

		if ($this->totalNumberOfTests !== 1) {
			echo $this->getReRunLink($test->getName());
		}
  		echo ' <strong class="testName">' . $this->prettifyTestMethod($test->getName()) . '</strong><br />';
  		$this->memoryUsageStartOfTest = memory_get_usage();
    }

	/**
	 * A test ended.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 */
	public function endTest(PHPUnit_Framework_Test $test, $time) {
		$this->memoryUsageEndOfTest = memory_get_usage();
		$this->currentTestNumber++;
		$percentDone = intval(($this->currentTestNumber / $this->totalNumberOfTests) * 100);
		$leakedMemory = ($this->memoryUsageEndOfTest - $this->memoryUsageStartOfTest);
		$this->totalLeakedMemory += $leakedMemory;

		if ($test instanceof PHPUnit_Framework_TestCase) {
			$this->testAssertions += $test->getNumAssertions();
		}

		if ($this->enableShowMemoryAndTime === true) {
			echo '<span class="memory-leak small-font"><strong>Memory leak:</strong> '.t3lib_div::formatSize($leakedMemory).'B </span>';
			echo '<span class="time-usages small-font"><strong>Time:</strong> '.sprintf('%.4f', $time).' sec.</span><br />';
		}
		echo '</div>';
		echo '<script type="text/javascript">document.getElementById("progress-bar").style.width = "'.$percentDone.'%";</script>';
		echo '<script type="text/javascript">document.getElementById("transparent-bar").style.width = "'.(100-$percentDone).'%";</script>';
		flush(); // TODO: Should fflush() from PHPUnit be used here?
	}

    /**
     * Returns the first trace information which is not caused by the PHPUnit file
     * "Framework/Assert.php".
     *
     * @param 	array	$traceArr: The trace array
     * @return	array	Trace information
     */
	protected function getFirstNonPHPUnitTrace (array $traceArr) {
		$testCaseTraceArr = array();
		foreach ($traceArr as $singleTraceArr) {
			if (!stristr($singleTraceArr['file'], 'Framework/Assert.php')) {
				$testCaseTraceArr = $singleTraceArr;
				break;
			}
		}
		return $testCaseTraceArr;
	}

	private function getReRunLink ($testName) {
		$iconImg = '<img style="vertical-align: middle; border: 1px solid #fff;" src="'.t3lib_extMgm::extRelPath('phpunit').'mod1/runner.gif" alt="Run this test only" />';
		return '<a href="'.$this->getReRunUrl($testName).'" title="Run this test only">'.$iconImg.'</a>';
	}

	private function getReRunUrl ($testName) {
		$baseUrl = 'mod.php?M=tools_txphpunitbeM1';
		$options = 'command=runsingletest&amp;testname='.$testName.'('.$this->currentTestCaseName.')';
		return $baseUrl.'&amp;'.$options;
	}

    /**
     * Prettifies the name of a test method.
     *
     * @param  string  $testName
     * @return string
     * @author Michael Klapper <michael.klapper@aoemedia.de>
     */
    protected function prettifyTestMethod($testName) {
		$content = '';

		if ($this->useHumanReadableTextFormat) {
			$this->NamePrettifier->setPrefix('test');
			$this->NamePrettifier->setSuffix(null);
			$content = $this->NamePrettifier->prettifyTestMethod (
				str_replace('test_', '', $testName) // this is required because the "setPrefix" work not very well with the prefix "test_"
			);
    	} else {
    		$content = $testName;
    	}

    	return $content;
    }

    /**
     * Prettifies the name of a test class.
     *
     * @param  string  $testClass
     * @return string
     * @author Michael Klapper <michael.klapper@aoemedia.de>
     */
    protected function prettifyTestClass($testClass) {
		$content = '';

		if ($this->useHumanReadableTextFormat) {
			$this->NamePrettifier->setPrefix('tx');
			$this->NamePrettifier->setSuffix('testcase');
			$content = $this->NamePrettifier->prettifyTestClass (
				str_replace('_', ' ', $testClass)
			);
    	} else {
    		$content = $testClass;
    	}

    	return $content;
    }

	/**
	 * Retrieve the collected amount of processed assertions.
	 *
	 * @return integer
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function assertionCount() {
		return $this->testAssertions;
	}
}
?>