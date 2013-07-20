<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
	private $fixture = NULL;

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

		$this->fixture = $this->createAccessibleProxy();

		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->fixture->injectExtensionSettingsService($this->extensionSettingsService);

		$this->fixturesPath = t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Service/Fixtures/';
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture, $this->extensionSettingsService);

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
		$this->assertTrue(
			$this->createAccessibleProxy() instanceof Tx_Phpunit_Service_TestFinder
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertTrue(
			$this->fixture instanceof t3lib_Singleton
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/') || file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/ only.');
		}

		$this->assertSame(
			'typo3_src/tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			'tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/') || file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/ only.');
		}

		$this->assertSame(
			PATH_site . 'typo3_src/tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			PATH_site . 'tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsInCoreSourceInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsDirectlyInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForNoCoreTestsReturnsFalse() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertFalse(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function getTestableForKeyForEmptyKeyThrowsException() {
		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$fixture->getTestableForKey('');
	}

	/**
	 * @test
	 */
	public function getTestableForKeyForExistingKeyReturnsTestableForKey() {
		$testable = new Tx_Phpunit_Testable();

		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')->will($this->returnValue(array('foo' => $testable)));

		$this->assertSame(
			$testable,
			$fixture->getTestableForKey('foo')
		);
	}

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 */
	public function getTestableForKeyForInexistentKeyThrowsException() {
		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$fixture->getTestableForKey('bar');
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForEmptyKeyReturnsFalse() {
		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertFalse(
			$fixture->existsTestableForKey('')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForExistingKeyReturnsTrue() {
		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertTrue(
			$fixture->existsTestableForKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableForKeyForInexistentKeyReturnsFalse() {
		/** @var $fixture Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$fixture->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_Testable())));

		$this->assertFalse(
			$fixture->existsTestableForKey('bar')
		);
	}


	/*
	 * Tests concerning getTestablesForEverything
	 */

	/**
	 * @test
	 */
	public function getTestablesForEverythingForNoCoreTestsAndNoExtensionTestsReturnsEmptyArray() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject  */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestableForCore')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('getTestablesForExtensions')->will($this->returnValue(array()));

		$this->assertSame(
			array(),
			$testFinder->getTestablesForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForEverythingForCoreTestsAndNoExtensionTestsReturnsCoreTests() {
		$coreTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject  */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestableForCore')
			->will($this->returnValue(array(Tx_Phpunit_Testable::CORE_KEY => $coreTests)));
		$testFinder->expects($this->once())->method('getTestablesForExtensions')->will($this->returnValue(array()));

		$this->assertSame(
			array(Tx_Phpunit_Testable::CORE_KEY => $coreTests),
			$testFinder->getTestablesForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForEverythingForNoCoreTestsAndExtensionTestsReturnsExtensionTests() {
		$extensionTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestableForCore')
			->will($this->returnValue(array()));
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
	public function getTestablesForEverythingForCoreTestsAndExtensionTestsReturnsCoreAndExtensionTestsWithCoreTestsLast() {
		$coreTests = new Tx_Phpunit_Testable();
		$extensionTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->once())->method('getTestableForCore')
			->will($this->returnValue(array(Tx_Phpunit_Testable::CORE_KEY => $coreTests)));
		$testFinder->expects($this->once())->method('getTestablesForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			array(
				'foo' => $extensionTests,
				Tx_Phpunit_Testable::CORE_KEY => $coreTests,
			),
			$testFinder->getTestablesForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForEverythingForCoreTestsAndExtensionCalledTwoTimesReturnsSameData() {
		$coreTests = new Tx_Phpunit_Testable();
		$extensionTests = new Tx_Phpunit_Testable();

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableForCore', 'getTestablesForExtensions'));
		$testFinder->expects($this->any())->method('getTestableForCore')
			->will($this->returnValue(array(Tx_Phpunit_Testable::CORE_KEY => $coreTests)));
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


	/*
	 * Tests concerning getTestableForCore
	 */

	/**
	 * @test
	 */
	public function getTestableForCoreForNoCoreTestsReturnsEmptyArray() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(FALSE));

		$this->assertSame(
			array(),
			$testFinder->getTestableForCore()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsExactlyOneTestableInstanceUsingCoreArrayKey() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$result = $testFinder->getTestableForCore();

		$this->assertSame(
			1,
			count($result),
			'The return array does not have exactly one element.'
		);
		$this->assertInstanceOf(
			'Tx_Phpunit_Testable',
			$result[Tx_Phpunit_Testable::CORE_KEY]
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithCoreType() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			Tx_Phpunit_Testable::TYPE_CORE,
			$testable->getType()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithTypo3Key() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			Tx_Phpunit_Testable::CORE_KEY,
			$testable->getKey()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithTypo3CoreTitle() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			'TYPO3 Core',
			$testable->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithSitePath() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			PATH_site,
			$testable->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithCoreTestsPath() {
		$coreTestsPath = '/core/tests/';

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue($coreTestsPath));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			$coreTestsPath,
			$testable->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableForCoreExistingCoreTestsReturnsTestableWithTypo3IconPath() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestableForCore());
		$this->assertSame(
			t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png',
			$testable->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysOfLoadedExtensions() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
			$this->markTestSkipped('This test is available in TYPO3 6.0 and above.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extListArray'] = array('bar');
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

		$this->assertContains(
			'bar',
			$this->fixture->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysOfLoadedExtensionsBelowVersionSix() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6000000) {
			$this->markTestSkipped('This test is available in TYPO3 below version 6.0.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = 'bar';
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

		$this->assertContains(
			'bar',
			$this->fixture->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysOfRequiredExtensions() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = '';
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = 'foo';

		$this->assertContains(
			'foo',
			$this->fixture->getLoadedExtensionKeys()
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
			$this->fixture->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysThatAreBothLoadedAndRequiredOnlyOnce() {
		if (!method_exists('t3lib_extMgm', 'getLoadedExtensionListArray')) {
			$this->markTestSkipped('This test is available in TYPO3 6.0 and above.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extListArray'] = array('foo');
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = array('foo');

		$this->assertSame(
			explode(',', REQUIRED_EXTENSIONS . ',foo'),
			$this->fixture->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysThatAreBothLoadedAndRequiredOnlyOnceBelowVersionSix() {
		if (method_exists('t3lib_extMgm', 'getLoadedExtensionListArray')) {
			$this->markTestSkipped('This test is available in TYPO3 below version 6.0.');
		}

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = 'foo';
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = 'foo';

		$this->assertSame(
			array('foo'),
			array_filter($this->fixture->getLoadedExtensionKeys(), array($this, 'keepOnlyFooElements'))
		);
	}

	/**
	 * Call-back function for checking whether $element is "Foo".
	 *
	 * @param string $element element to check, may be empty
	 *
	 * @return boolean TRUE if $element is == "foo", FALSE otherwise
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
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoExcludedExtensionsReturnsEmptyArray() {
		$this->extensionSettingsService->set('excludeextensions', '');

		$this->assertSame(
			array(),
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoPhpUnitConfigurationReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getDummyExtensionKeysReturnsKeysOfPhpUnitDummyExtensions() {
		$this->assertSame(
			array('aaa', 'bbb', 'ccc', 'ddd'),
			$this->fixture->getDummyExtensionKeys()
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
		$this->fixture->findTestsPathForExtension('');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function findTestsPathForExtensionForExtensionWithoutTestsPathThrowsException() {
		if (!t3lib_extMgm::isLoaded('ccc')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "ccc" from Tests/Unit/Fixtures/Extensions/ is installed.'
			);
		}

		$this->fixture->findTestsPathForExtension('ccc');
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithUpperFirstTestsDirectoryReturnsThatDirectory() {
		$this->assertSame(
			strtolower(t3lib_extMgm::extPath('phpunit') . 'Tests/'),
			strtolower($this->fixture->findTestsPathForExtension('phpunit'))
		);
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithLowerCaseTestsDirectoryReturnsThatDirectory() {
		if (!t3lib_extMgm::isLoaded('bbb')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "bbb" from Tests/Unit/Fixtures/Extensions/ is installed.'
			);
		}

		$this->assertSame(
			strtolower(t3lib_extMgm::extPath('bbb') . 'tests/'),
			strtolower($this->fixture->findTestsPathForExtension('bbb'))
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
	public function getTestablesForExtensionsProvidesTestableInstanceWithExtensionType() {
		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			Tx_Phpunit_Testable::TYPE_EXTENSION,
			$testable->getType()
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
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

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
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

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
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			t3lib_extMgm::extPath('phpunit'),
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
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			t3lib_extMgm::extPath('phpunit') . 'Tests/',
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
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			t3lib_extMgm::extRelPath('phpunit') . 'ext_icon.gif',
			$testable->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestablesForExtensionsWithPngIconProvidesTestableInstanceWithIconPath() {
		if (!t3lib_extMgm::isLoaded('user_phpunittest')) {
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
			->with('user_phpunittest')->will($this->returnValue(t3lib_extMgm::extPath('user_phpunittest') . 'Tests/'));

		/** @var $testable Tx_Phpunit_Testable */
		$testable = array_pop($testFinder->getTestablesForExtensions());
		$this->assertSame(
			t3lib_extMgm::extRelPath('user_phpunittest') . 'ext_icon.png',
			$testable->getIconPath()
		);
	}
}
?>