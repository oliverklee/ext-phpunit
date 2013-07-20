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
class Tx_Phpunit_Service_UserSettingsServiceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_UserSettingsService
	 */
	protected $fixture = NULL;

	/**
	 * backup of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackup = NULL;

	public function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];
		$GLOBALS['BE_USER'] = $this->getMock('t3lib_beUserAuth');

		$this->fixture = new Tx_Phpunit_Service_UserSettingsService();
	}

	public function tearDown() {
		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		unset($this->fixture, $this->backEndUserBackup);
	}

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertInstanceOf(
			't3lib_Singleton',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function classIsSingletonUserSettings() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Interface_UserSettingsService',
			$this->fixture
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanForMissingValueReturnsFalse() {
		$this->assertFalse(
			$this->fixture->getAsBoolean('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnFalseFromUserSettings() {
		$key = 'foo';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = FALSE;

		$this->assertFalse(
			$this->fixture->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnTrueFromUserSettings() {
		$key = 'foo';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = TRUE;

		$this->assertTrue(
			$this->fixture->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnOneStringFromUserSettingsAsTrue() {
		$key = 'foo';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = '1';

		$this->assertTrue(
			$this->fixture->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetBooleanValueToFalse() {
		$key = 'foo';
		$this->fixture->set($key, FALSE);

		$this->assertFalse(
			$this->fixture->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetBooleanValueToTrue() {
		$key = 'foo';
		$this->fixture->set($key, TRUE);

		$this->assertTrue(
			$this->fixture->getAsBoolean($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForMissingValueReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->getAsInteger('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForExistingValueReturnsValueFromUserSettings() {
		$key = 'foo';
		$value = 42;
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = $value;

		$this->assertSame(
			$value,
			$this->fixture->getAsInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsIntegerForExistingStringValueReturnsIntegerValueFromUserSettings() {
		$key = 'foo';
		$value = 42;
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = (string) $value;

		$this->assertSame(
			$value,
			$this->fixture->getAsInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetIntegerValue() {
		$key = 'foo';
		$value = 9;
		$this->fixture->set($key, $value);

		$this->assertSame(
			$value,
			$this->fixture->getAsInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$key = 'foo';
		$value = 0;
		$this->fixture->set($key, $value);

		$this->assertFalse(
			$this->fixture->hasInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$key = 'foo';
		$value = 2;
		$this->fixture->set($key, $value);

		$this->assertTrue(
			$this->fixture->hasInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$key = 'foo';
		$value = -1;
		$this->fixture->set($key, $value);

		$this->assertTrue(
			$this->fixture->hasInteger($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForMissingValueReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getAsString('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingValueReturnsValueFromUserSettings() {
		$key = 'foo';
		$value = 'bar';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = $value;

		$this->assertSame(
			$value,
			$this->fixture->getAsString($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingIntegerValueReturnsStringValueFromUserSettings() {
		$key = 'foo';
		$value = '42';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = intval($value);

		$this->assertSame(
			$value,
			$this->fixture->getAsString($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetStringValue() {
		$key = 'foo';
		$value = 'Hello world!';
		$this->fixture->set($key, $value);

		$this->assertSame(
			$value,
			$this->fixture->getAsString($key)
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$key = 'foo';
		$value = '';
		$this->fixture->set($key, $value);

		$this->assertFalse(
			$this->fixture->hasString($key)
		);
	}

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringReturnsTrue() {
		$key = 'foo';
		$value = 'bar';
		$this->fixture->set($key, $value);

		$this->assertTrue(
			$this->fixture->hasString($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayForMissingValueReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getAsArray('foo')
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayForExistingValueReturnsValueFromUserSettings() {
		$key = 'foo';
		$value = array('foo', 'bar');
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = $value;

		$this->assertSame(
			$value,
			$this->fixture->getAsArray($key)
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayForExistingIntegerValueReturnsEmptyArray() {
		$key = 'foo';
		$GLOBALS['BE_USER']->uc['Tx_Phpunit_BackEndSettings'][$key] = 42;

		$this->assertSame(
			array(),
			$this->fixture->getAsArray($key)
		);
	}

	/**
	 * @test
	 */
	public function setCanSetArrayValue() {
		$key = 'foo';
		$value = array('hello', 'world');
		$this->fixture->set($key, $value);

		$this->assertSame(
			$value,
			$this->fixture->getAsArray($key)
		);
	}

	/**
	 * @test
	 */
	public function setWritesUserSettings() {
		$GLOBALS['BE_USER']->expects($this->once())->method('writeUC');

		$this->fixture->set('foo', 'bar');
	}
}
?>