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
class Tx_Phpunit_Service_ExtensionSettingsServiceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_ExtensionSettingsService
	 */
	protected $fixture = NULL;

	/**
	 * @var string
	 */
	private $extensionConfigurationBackup = NULL;

	/**
	 * @var array
	 */
	protected $testConfiguration = array(
		'testValueString' => 'Hello world!',
		'testValueEmptyString' => '',
		'testValuePositiveInteger' => 42,
		'testValueZeroInteger' => 0,
		'testValueOneInteger' => 1,
		'testValueNegativeInteger' => -1,
		'testValueTrue' => TRUE,
		'testValueFalse' => FALSE,
		'testValueArray' => array('foo', 'bar'),
	);

	public function setUp() {
		$this->extensionConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'];
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['phpunit'] = serialize($this->testConfiguration);

		$this->fixture = new Tx_Phpunit_Service_ExtensionSettingsService();
	}

	public function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'] = $this->extensionConfigurationBackup;

		unset($this->fixture, $this->extensionConfigurationBackup);
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
	public function classIsSingletonExtensionSettings() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Interface_ExtensionSettingsService',
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
	public function getAsBooleanCanReturnFalseFromExtensionSettings() {
		$this->assertFalse(
			$this->fixture->getAsBoolean('testValueFalse')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnTrueFromExtensionSettings() {
		$this->assertTrue(
			$this->fixture->getAsBoolean('testValueTrue')
		);
	}

	/**
	 * @test
	 */
	public function getAsBooleanCanReturnOneStringFromExtensionSettingsAsTrue() {
		$this->assertTrue(
			$this->fixture->getAsBoolean('testValueOneInteger')
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
	public function getAsIntegerForExistingValueReturnsValueFromExtensionSettings() {
		$this->assertSame(
			42,
			$this->fixture->getAsInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForZeroReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasInteger('testValueZeroInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForPositiveIntegerReturnsTrue() {
		$this->assertTrue(
			$this->fixture->hasInteger('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasIntegerForNegativeIntegerReturnsTrue() {
		$this->assertTrue(
			$this->fixture->hasInteger('testValueNegativeInteger')
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
	public function getAsStringForExistingValueReturnsValueFromExtensionSettings() {
		$this->assertSame(
			'Hello world!',
			$this->fixture->getAsString('testValueString')
		);
	}

	/**
	 * @test
	 */
	public function getAsStringForExistingIntegerValueReturnsStringValueFromExtensionSettings() {
		$this->assertSame(
			'42',
			$this->fixture->getAsString('testValuePositiveInteger')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForEmptyStringReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasString('testValueEmptyString')
		);
	}

	/**
	 * @test
	 */
	public function hasStringForNonEmptyStringReturnsTrue() {
		$this->assertTrue(
			$this->fixture->hasString('testValueString')
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
	public function getAsArrayForExistingValueReturnsValueFromExtensionSettings() {
		$this->assertSame(
			array('foo', 'bar'),
			$this->fixture->getAsArray('testValueArray')
		);
	}

	/**
	 * @test
	 */
	public function getAsArrayForExistingIntegerValueReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getAsArray('testValuePositiveInteger')
		);
	}
}
?>