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
		$this->fixture = $this->createAccessibleProxy();
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
	 * @return Tx_Phpunit_BackEnd_TestListener an accessible proxy
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_BackEnd_TestListenerAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_BackEnd_TestListener {' .
				'  public function isTestCaseFileName($path) {' .
				'    return parent::isTestCaseFileName($path);' .
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

		return new $className();
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesTestListenerSubclass() {
		$this->assertTrue(
			$this->createAccessibleProxy() instanceof Tx_Phpunit_BackEnd_TestListener
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function prettifyTestMethodForTestPrefixByDefaultReturnsCamelCaseNameUnchanged() {
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
	public function prettifyTestMethodForTestPrefixWithUnderscoreByDefaultReturnsCamelCaseNameUnchanged() {
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
	public function prettifyTestMethodByDefaultReturnsCamelCaseNameUnchanged() {
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
	public function prettifyTestClassByDefaultReturnsCamelCaseNameUnchanged() {
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
	public function prettifyTestClassForExtbaseClassNameByDefaultReturnsCamelCaseNameUnchanged() {
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
			'Tx Phpunit BackEnd TestListener',
			$this->fixture->prettifyTestClass('Tx_Phpunit_BackEnd_TestListenerTest')
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