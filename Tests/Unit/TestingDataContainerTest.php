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
class Tx_Phpunit_TestingDataContainerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $subject = NULL;

	public function setUp() {
		$this->subject = new Tx_Phpunit_TestingDataContainer();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function classIsSingletonUserSettings() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Interface_UserSettingsService',
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
	public function setCanSetBooleanValueToFalse() {
		$key = 'foo';
		$this->subject->set($key, FALSE);

		$this->assertFalse(
			$this->subject->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetBooleanValueToTrue() {
		$key = 'foo';
		$this->subject->set($key, TRUE);

		$this->assertTrue(
			$this->subject->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnOneStringAsTrue() {
		$key = 'foo';
		$this->subject->set($key, '1');

		$this->assertTrue(
			$this->subject->getAsBoolean($key)
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
	public function setCanSetIntegerValue() {
		$key = 'foo';
		$value = 42;
		$this->subject->set($key, $value);

		$this->assertSame(
			$value,
			$this->subject->getAsInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$key = 'foo';
		$value = 0;
		$this->subject->set($key, $value);

		$this->assertFalse(
			$this->subject->hasInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$key = 'foo';
		$value = 2;
		$this->subject->set($key, $value);

		$this->assertTrue(
			$this->subject->hasInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$key = 'foo';
		$value = -1;
		$this->subject->set($key, $value);

		$this->assertTrue(
			$this->subject->hasInteger($key)
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
	public function setCanSetStringValue() {
		$key = 'foo';
		$value = 'Hello world!';
		$this->subject->set($key, $value);

		$this->assertSame(
			$value,
			$this->subject->getAsString($key)
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$key = 'foo';
		$value = '';
		$this->subject->set($key, $value);

		$this->assertFalse(
			$this->subject->hasString($key)
		);
	}

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringReturnsTrue() {
		$key = 'foo';
		$value = 'bar';
		$this->subject->set($key, $value);

		$this->assertTrue(
			$this->subject->hasString($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayForMissingValueReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getAsArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function setCanSetArrayValue() {
		$key = 'foo';
		$value = array('foo', 'foobar');
		$this->subject->set($key, $value);

		$this->assertSame(
			$value,
			$this->subject->getAsArray($key)
		);
	}
}