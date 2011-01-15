<?php
/***************************************************************
* Copyright notice
*
* (c) 2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Phpunit_BackEnd_TestListener class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_TestListenerTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Phpunit_BackEnd_TestListener
	 */
	private $fixture;

	public function setUp() {
		$fixtureClassName = $this->createAccessibleProxy();
		$this->fixture = new $fixtureClassName();
	}

	public function tearDown() {
		$this->fixture->__destruct();

		unset($this->fixture);
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
				'  public function output($output) {' .
				'    parent::output($output);' .
				'  }' .
				'}'
			);
		}

		return $className;
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesTestListenerSubclass() {
		$className = $this->createAccessibleProxy();

		$this->assertTrue(
			(new $className()) instanceof Tx_Phpunit_BackEnd_TestListener
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function endTestAddsTestAssertionsToTotalAssertionCount() {
		$fixture = $this->getMock(
			'Tx_Phpunit_BackEnd_TestListener', array('output', 'flushOutputBuffer')
		);

		$testCase1 = $this->getMock('PHPUnit_Framework_TestCase', array('getNumAssertions'));
		$testCase1->expects($this->once())->method('getNumAssertions')->will($this->returnValue(1));

		$fixture->endTest($testCase1);
		$this->assertEquals(
			1,
			$fixture->assertionCount(),
			'The assertions of the first test case have not been counted.'
		);

		$testCase2 = $this->getMock('PHPUnit_Framework_TestCase', array('getNumAssertions'));
		$testCase2->expects($this->once())->method('getNumAssertions')->will($this->returnValue(4));

		$fixture->endTest($testCase2);
		$this->assertEquals(
			5,
			$fixture->assertionCount(),
			'The assertions of the second test case have not been counted.'
		);
	}

	/**
	 * @test
	 */
	public function endTestForTestCaseInstanceLeavesAssertionCountUnchanged() {
		$fixture = $this->getMock(
			'Tx_Phpunit_BackEnd_TestListener', array('output', 'flushOutputBuffer')
		);

		$test = $this->getMock('PHPUnit_Framework_TestCase');

		$fixture->endTest($test);
		$this->assertEquals(
			0,
			$fixture->assertionCount()
		);
	}

	/**
	 * @test
	 */
	public function endTestForPlainTestInstanceLeavesAssertionCountUnchanged() {
		$fixture = $this->getMock(
			'Tx_Phpunit_BackEnd_TestListener', array('output', 'flushOutputBuffer')
		);

		$test = $this->getMock('PHPUnit_Framework_Test');

		$fixture->endTest($test);
		$this->assertEquals(
			0,
			$fixture->assertionCount()
		);
	}

	/**
	 * @test
	 */
	public function createReRunLinkContainsLinkToReRunUrl() {
		$reRunUrl = 'index.php?reRun=1&amp;foo=bar';

		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

		$fixture = $this->getMock($this->createAccessibleProxy(), array('createReRunUrl'));
		$fixture->expects($this->once())->method('createReRunUrl')
			->will($this->returnValue($reRunUrl));

		$this->assertContains(
			'<a href="' . $reRunUrl . '"',
			$fixture->createReRunLink($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsModuleParameter() {
		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

		$this->assertContains(
			'mod.php?M=tools_txphpunitbeM1',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsRunSingleCommand() {
		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

		$this->assertContains(
			'command=runsingletest',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsTestCaseFileName() {
		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

		$this->fixture->setTestSuiteName('myTestCase');

		$this->assertContains(
			'testCaseFile=myTestCase',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlContainsTestCaseName() {
		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

		$this->fixture->setTestSuiteName('myTestCase');

		$this->assertContains(
			'testname=myTest',
			$this->fixture->createReRunUrl($test)
		);
	}

	/**
	 * @test
	 */
	public function createReRunUrlEscapesAmpersands() {
		$test = $this->getMock(
			'PHPUnit_Framework_TestCase', array(), array('myTest')
		);

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

	/**
	 * @test
	 */
	public function outputOutputsOutput() {
		$output = 'Hello world!';

		ob_start();
		$this->fixture->output($output);

		$this->assertSame(
			$output,
			ob_get_contents()
		);

		ob_end_clean();
	}
}
?>