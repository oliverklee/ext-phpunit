<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
 *
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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_RequestTest extends Tx_Phpunit_TestCase {
	/**
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * Exclude TYPO3_DB from backup/ restore of $GLOBALS
	 * because resource types cannot be handled during serializing.
	 *
	 * @var array
	 */
	protected $backupGlobalsBlacklist = array('TYPO3_DB');

	/**
	 * @var Tx_Phpunit_BackEnd_Request
	 */
	protected $subject = NULL;

	/**
	 * @var array
	 */
	protected $testData = array(
		'testValueString' => 'Hello world!',
		'testValueEmptyString' => '',
		'testValuePositiveInteger' => 42,
		'testValueZeroInteger' => 0,
		'testValueOneInteger' => 1,
		'testValueNegativeInteger' => -1,
		'testValueTrue' => TRUE,
		'testValueFalse' => FALSE,
	);

	public function setUp() {
		$GLOBALS['_GET'] = array();
		$GLOBALS['_POST'] = array();

		$this->subject = new Tx_Phpunit_BackEnd_Request();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function classIsRequest() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Interface_Request',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanForMissingValueReturnsFalse() {
		$this->assertFalse(
			$this->subject->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnFalseFromGet() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->getAsBoolean('testValueFalse')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnTrueFromGet() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->getAsBoolean('testValueTrue')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnOneStringFromGetAsTrue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->getAsBoolean('testValueOneInteger')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForMissingValueReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForExistingValueFromGetReturnsValue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			42,
			$this->subject->getAsInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroFromGetReturnsFalse() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->hasInteger('testValueZeroInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerFromGetReturnsTrue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerFromGetReturnsTrue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasInteger('testValueNegativeInteger')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForMissingValueReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingValueFromGetReturnsValue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			'Hello world!',
			$this->subject->getAsString('testValueString')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingIntegerValueFromGetReturnsStringValue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			'42',
			$this->subject->getAsString('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringFromGetReturnsFalse() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->hasString('testValueEmptyString')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringFromGetReturnsTrue() {
		$GLOBALS['_GET']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasString('testValueString')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnFalseFromPost() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->getAsBoolean('testValueFalse')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnTrueFromPost() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->getAsBoolean('testValueTrue')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnOneStringFromPostAsTrue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->getAsBoolean('testValueOneInteger')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForExistingValueFromPostReturnsValue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			42,
			$this->subject->getAsInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroFromPostReturnsFalse() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->hasInteger('testValueZeroInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerFromPostReturnsTrue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerFromPostReturnsTrue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasInteger('testValueNegativeInteger')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingValueFromPostReturnsValue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			'Hello world!',
			$this->subject->getAsString('testValueString')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingIntegerValueFromPostReturnsStringValue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertSame(
			'42',
			$this->subject->getAsString('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringFromPostReturnsFalse() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertFalse(
			$this->subject->hasString('testValueEmptyString')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringFromPostReturnsTrue() {
		$GLOBALS['_POST']['tx_phpunit'] = $this->testData;

		$this->assertTrue(
			$this->subject->hasString('testValueString')
		);
	}

	/**
	 * @test
	 */
	public function postHasPrecedenceOverGet() {
		$GLOBALS['_GET']['tx_phpunit'] = array('foo' => 'getValue');
		$GLOBALS['_POST']['tx_phpunit'] = array('foo' => 'postValue');

		$this->assertSame(
			'postValue',
			$this->subject->getAsString('foo')
		);
	}
}