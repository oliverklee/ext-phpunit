<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinderTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_TestFinder
	 */
	private $subject = NULL;

	/**
	 * the absolute path to the fixtures directory for this testcase
	 *
	 * @var string
	 */
	private $fixturesPath = '';

	/**
	 * backup of $GLOBALS['TYPO3_CONF_VARS']
	 *
	 * @var array
	 */
	private $typo3ConfigurationVariablesBackup = array();

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $extensionSettingsService = NULL;

	public function setUp() {
		$this->typo3ConfigurationVariablesBackup = $GLOBALS['TYPO3_CONF_VARS'];

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = '';
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

		$this->subject = $this->createAccessibleProxy();

		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectExtensionSettingsService($this->extensionSettingsService);

		$this->fixturesPath = ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Service/Fixtures/';
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject, $this->extensionSettingsService);

		$GLOBALS['TYPO3_CONF_VARS'] = $this->typo3ConfigurationVariablesBackup;
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a subclass Tx_Phpunit_Service_TestFinder with the protected
	 * functions made public.
	 *
	 * @return Tx_Phpunit_Service_TestFinder an accessible proxy
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_Service_TestFinderAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_Service_TestFinder {' .
				'  public function getLoadedExtensionKeys() {' .
				'    return parent::getLoadedExtensionKeys();' .
				'  }' .
				'  public function getExcludedExtensionKeys() {' .
				'    return parent::getExcludedExtensionKeys();' .
				'  }' .
				'  public function getDummyExtensionKeys() {' .
				'    return parent::getDummyExtensionKeys();' .
				'  }' .
				'  public function findTestsPathForExtension($extensionKey) {' .
				'    return parent::findTestsPathForExtension($extensionKey);' .
				'  }' .
				'}'
			);
		}

		return new $className();
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesTestFinderSubclass() {
		$this->assertInstanceOf('Tx_Phpunit_Service_TestFinder', $this->createAccessibleProxy());
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertInstanceOf('TYPO3\\CMS\\Core\\SingletonInterface', $this->subject);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function getTestableForKeyForEmptyKeyThrowsException() {
		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$subject->getTestableForKey('');
	}

	/**
	 * @test
	 */
	public function getTestableForKeyForExistingKeyReturnsTestableForKey() {
		$testable = new Tx_Phpunit_Testable();

		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')->will($this->returnValue(array('foo' => $testable)));

		$this->assertSame(
			$testable,
			$subject->getTestableForKey('foo')
		);
	}

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 */
	public function getTestableForKeyForInexistentKeyThrowsException() {
		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$subject->getTestableForKey('bar');
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForEmptyKeyReturnsFalse() {
		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertFalse(
			$subject->existsTestableForKey('')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForExistingKeyReturnsTrue() {
		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertTrue(
			$subject->existsTestableForKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForInexistentKeyReturnsFalse() {
		/** @var $subject Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$subject->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertFalse(
			$subject->existsTestableForKey('bar')
		);
	}


	/*
	 * Tests concerning getTestablesForEverything
	 */

	/**
	 * @test
	 */
	public function getTestablesForEverythingNoExtensionTestsReturnsEmptyArray() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject  */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestablesForExtensions')->will($this->returnValue(array()));

		$this->assertSame(
			array(),
			$testFinder->getTestablesForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForEverythingForExtensionTestsReturnsExtensionTests() {
		$extensionTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestablesForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			array('foo' => $extensionTests),
			$testFinder->getTestablesForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForEverythingCalledTwoTimesReturnsSameData() {
		$coreTests = new Tx_Phpunit_Testable();
		$extensionTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->any())->method('getTestablesForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			$testFinder->getTestablesForEverything(),
			$testFinder->getTestablesForEverything()
		);
	}

	/*
	 * Tests concerning existsTestableForAnything
	 */

	/**
	 * @test
	 */
	public function existsTestableForAnythingForNoTestablesReturnsFalse() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array()));

		$this->assertFalse(
			$testFinder->existsTestableForAnything()
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForAnythingForOneTestableReturnsTrue() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertTrue(
			$testFinder->existsTestableForAnything()
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForAnythingForTwoTestablessReturnsTrue() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable(), 'bar' => new Tx_Phpunit_Testable())));

		$this->assertTrue(
			$testFinder->existsTestableForAnything()
		);
	}


	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysOfAlwasRequiredExtensions() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = '';
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

		$this->assertContains(
			'cms',
			$this->subject->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsPhpUnitKey() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = 'phpunit';

		$this->assertArrayHasKey(
			'phpunit',
			array_flip($this->subject->getLoadedExtensionKeys())
		);
	}

	/**
	 * Call-back function for checking whether $element is "Foo".
	 *
	 * @param string $element element to check, may be empty
	 *
	 * @return bool TRUE if $element is == "foo", FALSE otherwise
	 */
	public function keepOnlyFooElements($element) {
		return ($element === 'foo');
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysReturnsKeysOfExcludedExtensions() {
		$this->extensionSettingsService->set('excludeextensions', 'foo,bar');

		$this->assertSame(
			array('foo', 'bar'),
			$this->subject->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoExcludedExtensionsReturnsEmptyArray() {
		$this->extensionSettingsService->set('excludeextensions', '');

		$this->assertSame(
			array(),
			$this->subject->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoPhpUnitConfigurationReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getDummyExtensionKeysReturnsKeysOfPhpUnitDummyExtensions() {
		$this->assertSame(
			array('aaa', 'bbb', 'ccc', 'ddd'),
			$this->subject->getDummyExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsCreatesTestableForSingleExtensionForInstalledExtensionsWithoutExcludedExtensions() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension',
				'createTestableForSingleExtension'
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar', 'foobar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array('foo', 'baz')));

		$testFinder->expects($this->at(2))->method('createTestableForSingleExtension')
			->with('bar')->will($this->returnValue(new Tx_Phpunit_Testable()));
		$testFinder->expects($this->at(3))->method('createTestableForSingleExtension')
			->with('foobar')->will($this->returnValue(new Tx_Phpunit_Testable()));

		$testFinder->getTestablesForExtensions();
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsCreatesTestableForSingleExtensionForInstalledExtensionsWithoutDummyExtensions() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'getDummyExtensionKeys',
				'findTestsPathForExtension', 'createTestableForSingleExtension',
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar', 'foobar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('getDummyExtensionKeys')->will($this->returnValue(array('foo', 'baz')));

		$testFinder->expects($this->at(3))->method('createTestableForSingleExtension')
			->with('bar')->will($this->returnValue(new Tx_Phpunit_Testable()));
		$testFinder->expects($this->at(4))->method('createTestableForSingleExtension')
			->with('foobar')->will($this->returnValue(new Tx_Phpunit_Testable()));

		$testFinder->getTestablesForExtensions();
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsSortsExtensionsByNsmeInAscendingOrder() {
		$testableForFoo = new Tx_Phpunit_Testable();
		$testableForFoo->setKey('foo');
		$testableForBar = new Tx_Phpunit_Testable();
		$testableForBar->setKey('bar');

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension',
				'createTestableForSingleExtension'
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));

		$testFinder->expects($this->at(2))->method('createTestableForSingleExtension')
			->with('foo')->will($this->returnValue($testableForFoo));
		$testFinder->expects($this->at(3))->method('createTestableForSingleExtension')
			->with('bar')->will($this->returnValue($testableForBar));

		$this->assertSame(
			array('bar' => $testableForBar, 'foo' => $testableForFoo),
			$testFinder->getTestablesForExtensions()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestsPathForExtensionForExtensionWithEmptyExtensionKeyThrowsException() {
		$this->subject->findTestsPathForExtension('');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function findTestsPathForExtensionForExtensionWithoutTestsPathThrowsException() {
		if (!ExtensionManagementUtility::isLoaded('ccc')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "ccc" from Tests/Unit/Fixtures/Extensions/ is installed.'
			);
		}

		$this->subject->findTestsPathForExtension('ccc');
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithUpperFirstTestsDirectoryReturnsThatDirectory() {
		$this->assertSame(
			strtolower(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'),
			strtolower($this->subject->findTestsPathForExtension('phpunit'))
		);
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithLowerCaseTestsDirectoryReturnsThatDirectory() {
		if (!ExtensionManagementUtility::isLoaded('bbb')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "bbb" from Tests/Unit/Fixtures/Extensions/ is installed.'
			);
		}

		$this->assertSame(
			strtolower(ExtensionManagementUtility::extPath('bbb') . 'tests/'),
			strtolower($this->subject->findTestsPathForExtension('bbb'))
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsForNoInstalledExtensionsReturnsEmptyArray() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getLoadedExtensionKeys'));
		$testFinder->injectExtensionSettingsService($this->extensionSettingsService);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array()));

		$this->assertSame(
			array(),
			$testFinder->getTestablesForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsForOneInstalledExtensionsWithTestsReturnsOneTestableInstance() {
		$testableInstance = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension',
				'createTestableForSingleExtension'
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('createTestableForSingleExtension')
			->with('foo')->will($this->returnValue($testableInstance));

		$this->assertSame(
			array('foo' => $testableInstance),
			$testFinder->getTestablesForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsForTwoInstalledExtensionsWithTestsReturnsTwoResults() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension',
				'createTestableForSingleExtension'
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->at(2))->method('createTestableForSingleExtension')
			->with('foo')->will($this->returnValue(new Tx_Phpunit_Testable()));
		$testFinder->expects($this->at(3))->method('createTestableForSingleExtension')
			->with('bar')->will($this->returnValue(new Tx_Phpunit_Testable()));

		$this->assertSame(
			2,
			count($testFinder->getTestablesForExtensions())
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsForOneInstalledExtensionsWithoutTestsReturnsEmptyArray() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('foo')->will($this->throwException(new Tx_Phpunit_Exception_NoTestsDirectory()));

		$this->assertSame(
			array(),
			$testFinder->getTestablesForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsForOneExtensionsWithoutTestsAndOneWithTestsReturnsFirstExtension() {
		$testableInstance = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array(
				'getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension',
				'createTestableForSingleExtension'
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->at(2))->method('createTestableForSingleExtension')
			->with('foo')->will($this->throwException(new Tx_Phpunit_Exception_NoTestsDirectory()));
		$testFinder->expects($this->at(3))->method('createTestableForSingleExtension')
			->with('bar')->will($this->returnValue($testableInstance));

		$this->assertSame(
			array('bar' => $testableInstance),
			$testFinder->getTestablesForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsProvidesTestableInstanceWithExtensionKey() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			'phpunit',
			$testable->getKey()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsProvidesTestableInstanceWithExtensionKeyAsTitleTitle() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			'phpunit',
			$testable->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsProvidesTestableInstanceWithCodePath() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			ExtensionManagementUtility::extPath('phpunit'),
			$testable->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsProvidesTestableInstanceWithTestsPath() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			ExtensionManagementUtility::extPath('phpunit') . 'Tests/',
			$testable->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsWithGifIconProvidesTestableInstanceWithIconPath() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			ExtensionManagementUtility::extRelPath('phpunit') . 'ext_icon.gif',
			$testable->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsWithPngIconProvidesTestableInstanceWithIconPath() {
		if (!ExtensionManagementUtility::isLoaded('user_phpunittest')) {
			$this->markTestSkipped(
				'The Extension user_phpunittest is not installed, but needs to be installed. ' .
					'Please install it from EXT:phpunit/Tests/Unit/Fixtures/Extensions/user_phpunittest/.'
			);
		}

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('user_phpunittest')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('user_phpunittest')->will($this->returnValue(ExtensionManagementUtility::extPath('user_phpunittest') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			ExtensionManagementUtility::extRelPath('user_phpunittest') . 'ext_icon.png',
			$testable->getIconPath()
		);
	}
}
