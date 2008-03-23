<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004 Robert Lemke (robert@typo3.org)
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

require_once ('PHPUnit/Framework/TestListener.php');

class tx_phpunit_testlistener implements PHPUnit_Framework_TestListener {

	protected	$resultArr = array();
	public		$totalNumberOfTestCases = 0;				// Set from outside
	protected	$currentTestNumber = 0;						// For counting the tests		
	
	/**
	 *	An error occurred.
	 *
	 *	@param  PHPUnit_Framework_Test $test
	 *	@param  Exception               $e
	 *	@access public
	 */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	$testCaseTraceArr = $this->getFirstNonPHPUnitTrace ($e->getTrace());
		$fileName = str_replace (PATH_site, '', $testCaseTraceArr['file']);		
    	echo '
			<script>setClass("progress-bar","hadError");</script>
			<span class="hadFailures">!</span> <strong>Error</strong> in test case <em>'.$test->getName().'</em>
			<br />in file<em> '.$fileName.'</em>
			<br />on line<em> '.$testCaseTraceArr['line'].'</em>:
			<div class="message">Message:<br>'.htmlspecialchars($e->getMessage()).'</div>';
		//	<div class="message">Trace:<br>'.htmlspecialchars($e->getTrace()).'</div>';
    	flush();
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
    	//$result = $test->createResult();
    	$testCaseTraceArr = $this->getFirstNonPHPUnitTrace ($e->getTrace());
		$fileName = str_replace (PATH_site, '', $testCaseTraceArr['file']);

    	echo '
			<script>setClass("progress-bar","hadFailure");</script>
			<script>setClass("testCaseNum-'.$this->currentTestNumber.'","testCaseFailure");</script>
			<script>setClass("tx_phpunit_testcase_nr_'.$this->currentTestNumber.'","testCaseFailure");</script>
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
    * @access public
    */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	echo '
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
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
    	// TODO: Implement addSkippedTest
    }
    
    /**
    * A testsuite started.
    *
    * @param  PHPUnit_Framework_TestSuite $suite
    * @access public
    */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
    	if ($suite->getName() !== 'tx_phpunit_basetestsuite') {
			echo '<h2 class="testSuiteName">Testsuite: '.$suite->getName().'</h2>';
			echo '<script>setClass("progress-bar","wasSuccessful");</script>';
    	}
    }

    /**
    * A testsuite ended.
    *
    * @param  PHPUnit_Framework_TestSuite $suite
    * @access public
    */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
    }

    /**
    * A test started.
    *
    * @param  PHPUnit_Framework_Test $test
    * @access public
    */
    public function startTest(PHPUnit_Framework_Test $test) {
    	set_time_limit(30); // A sinlge test has to take less than this or else PHP will timeout.    	
		echo '<div id="testCaseNum-'.$this->currentTestNumber.'" class="testcaseOutput">';
		echo '<script>setClass("tx_phpunit_testcase_nr_'.$this->currentTestNumber.'","wasSuccessful");</script>';
  		echo 'Test: <strong class"testName">'.$test->getName().'</strong><br />'; 
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time) {
    	$this->currentTestNumber++;
    	$percentDone = intval (($this->currentTestNumber / $this->totalNumberOfTestCases) * 100);

    	echo '</div>';
    	echo '<script>document.getElementById("progress-bar").style.width = "'.$percentDone.'%";</script>';
    	echo '<script>document.getElementById("transparent-bar").style.width = "'.(100-$percentDone).'%";</script>';
     	flush();
    }

    /**
     * Returns the first trace information which is not caused by the PHPUnit file
     * "Framework/Assert.php".
     *
     * @param 	array	$traceArr: The trace array
     * @return	array	Trace information
     * @access 	protected
     */
	protected function getFirstNonPHPUnitTrace (array $traceArr) {
		$testCaseTraceArr = array();
		foreach ($traceArr as $singleTraceArr) {
			if (!stristr ($singleTraceArr['file'], 'Framework/Assert.php')) {
				$testCaseTraceArr = $singleTraceArr;
				break;	
			}
		}
		return $testCaseTraceArr;
	}
}


?>