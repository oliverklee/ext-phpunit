<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2012 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_typo3 . 'template.php');
$GLOBALS['LANG']->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xml');

/**
 * Testcase for the Tx_Phpunit_BackEnd_Module class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_ModuleTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_Module
	 */
	private $fixture = NULL;

	/**
	 * @var Tx_PhpUnit_Service_FakeOutputService
	 */
	protected $outputService = NULL;

	/**
	 * @var Tx_Phpunit_Service_FakeSettingsService
	 */
	protected $userSettingsService = NULL;

	/**
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackup = NULL;

	/**
	 * backup of $_POST
	 *
	 * @var array
	 */
	private $postBackup = array();

	/**
	 * backup of $_GET
	 *
	 * @var array
	 */
	private $getBackup = array();

	public function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];
		$this->postBackup = $_POST;
		$this->getBackup = $_GET;
		$_POST = array();
		$_GET = array();

		$fixtureClassName = $this->createAccessibleProxy();
		$this->fixture = new $fixtureClassName();

		$this->outputService = new Tx_PhpUnit_Service_FakeOutputService();
		$this->fixture->injectOutputService($this->outputService);

		$this->userSettingsService = new Tx_Phpunit_Service_FakeSettingsService();
		$this->fixture->injectUserSettingsService($this->userSettingsService);
	}

	public function tearDown() {
		$this->fixture->__destruct();

		$_POST = $this->postBackup;
		$_GET = $this->getBackup;

		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		unset($this->fixture, $this->outputService, $this->userSettingsService, $this->backEndUserBackup);
	}

	/*
	 * Utility functions
	 */

	/**
	 * Creates a subclass Tx_Phpunit_BackEnd_Module with the protected functions
	 * made public.
	 *
	 * @return string the name of the accessible proxy class
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_BackEnd_ModuleAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_BackEnd_Module {' .
				'  public function getTestFinder() {' .
				'    return parent::getTestFinder();' .
				'  }' .
				'  public function runTests_render() {' .
				'    parent::runTests_render();' .
				'  }' .
				'  public function runTests_renderIntro() {' .
				'    parent::runTests_renderIntro();' .
				'  }' .
				'  public function createExtensionSelector() {' .
				'    return parent::createExtensionSelector();' .
				'  }' .
				'  public function createTestCaseSelector($extensionKey = \'\') {' .
				'    return parent::createTestCaseSelector($extensionKey);' .
				'  }' .
				'  public function findTestCasesInDir($directory) {' .
				'    return parent::findTestCasesInDir($directory);' .
				'  }' .
				'  public function loadRequiredTestClasses(array $paths) {' .
				'    parent::loadRequiredTestClasses($paths);' .
				'  }' .
				'  public function createIconStyle($extensionKey) {' .
				'    return parent::createIconStyle($extensionKey);' .
				'  }' .
				'  public function createAndInitializeTestListener() {' .
				'    return parent::createAndInitializeTestListener();' .
				'  }' .
				'  public function isAcceptedTestSuitClass($class) {' .
				'    return parent::isAcceptedTestSuitClass($class);' .
				'  }' .
				'}'
			);
		}

		return $className;
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesModuleSubclass() {
		$className = $this->createAccessibleProxy();

		$this->assertInstanceOf(
			'Tx_Phpunit_BackEnd_Module',
			new $className()
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function getTestFinderReturnsTestFinderInstance() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Service_TestFinder',
			$this->fixture->getTestFinder()
		);
	}

	/**
	 * @test
	 */
	public function mainForNoAdminBackEndUserShowsAdminRightsNeeded() {
		$GLOBALS['BE_USER']->user['admin'] = FALSE;

		$this->fixture->main();

		$this->assertContains(
			$GLOBALS['LANG']->getLL('admin_rights_needed'),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function mainForAdminBackEndUserRunsTests() {
		$GLOBALS['BE_USER']->user['admin'] = TRUE;

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('runTests_render'));
		$fixture->injectOutputService($this->outputService);
		$fixture->injectUserSettingsService($this->userSettingsService);
		$fixture->expects($this->once())->method('runTests_render');

		$fixture->main();
	}

	/**
	 * @test
	 */
	public function runTests_renderForEmptyCommandRendersIntro() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('runTests_renderIntro');

		$_GET['command'] = '';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForEmptyCommandNotRunsTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->never())->method('runTests_renderRunningTest');

		$_GET['command'] = '';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForInvalidCommandRendersIntro() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('runTests_renderIntro');

		$_GET['command'] = 'invalid';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForInvalidCommandNotRunsTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->never())->method('runTests_renderRunningTest');

		$_GET['command'] = 'invalid';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunAllTestsCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runalltests';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunTestCaseFileCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runTestCaseFile';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderForRunSingleTestCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('runTests_renderIntro', 'runTests_renderRunningTest')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('runTests_renderIntro');
		$fixture->expects($this->once())->method('runTests_renderRunningTest');

		$_GET['command'] = 'runsingletest';

		$fixture->runTests_render();
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForNoExtensionsWithTestSuitesShowsErrorMessage() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')
			->will($this->returnValue(FALSE));

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector', 'getTestFinder')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->any())->method('getTestFinder')->will($this->returnValue($testFinder));

		$fixture->runTests_renderIntro();

		$this->assertContains(
			$GLOBALS['LANG']->getLL('could_not_find_exts_with_tests'),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForNoExtensionsWithTestSuitesNotRendersExtensionSelector() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')
			->will($this->returnValue(FALSE));

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector', 'getTestFinder')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->any())->method('getTestFinder')->will($this->returnValue($testFinder));
		$fixture->expects($this->never())->method('createExtensionSelector')
			->will($this->returnValue('extension selector'));

		$fixture->runTests_renderIntro();
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForExistingExtensionsWithTestSuitesRendersExtensionSelector() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createExtensionSelector')
			->will($this->returnValue('extension selector'));

		$fixture->runTests_renderIntro();

		$this->assertContains(
			'extension selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForSelectedExtensionRendersTestCaseSelector() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestCaseSelector')
			->with($selectedExtension)->will($this->returnValue('test case selector'));

		$fixture->runTests_renderIntro();

		$this->assertContains(
			'test case selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForSelectedExtensionRendersTestSelector() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$fixture->runTests_renderIntro();

		$this->assertContains(
			'test selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function runTests_renderIntroForSelectedExtensionRendersCheckboxes() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$fixture->runTests_renderIntro();

		$this->assertContains(
			'test selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirReturnsEmptyArrayIfDirectoryDoesNotExist() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
		$notExistingDirectory = 'vfs://Foo/bar';

		$this->assertSame(
			array(),
			$this->fixture->findTestCasesInDir($notExistingDirectory)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirCallsFindTestCasesInDirectoryOfTestFinderObject() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
		$directory = 'vfs://Foo/';

		$testFinderMock = $this->getMock(
			'Tx_Phpunit_Service_TestFinder', array('findTestCaseFilesDirectory')
		);
		$testFinderMock->expects($this->once())->method('findTestCaseFilesDirectory')->with($directory);

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('getTestFinder'));
		$fixture->expects($this->once())->method('getTestFinder')
			->will($this->returnValue($testFinderMock));

		$fixture->findTestCasesInDir($directory);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirReturnsArrayWithFoundTestCaseFiles() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
		$directory = 'vfs://Foo/';
		$testFiles = array('class.test1Test.php', 'class.test2Test.php');

		$testFinderMock = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('findTestCaseFilesDirectory')
		);
		$testFinderMock->expects($this->once())->method('findTestCaseFilesDirectory')
			->will($this->returnValue($testFiles));

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('getTestFinder'));
		$fixture->expects($this->once())->method('getTestFinder')
			->will($this->returnValue($testFinderMock));

		$this->assertSame(
			array($directory => $testFiles),
			$fixture->findTestCasesInDir($directory)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsFileInFirstPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
					'LoadMe.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_BackEnd_Fixtures_LoadMe', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsSecondFileInFirstPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
					'LoadMe.php',
					'LoadMeToo.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_BackEnd_Fixtures_LoadMeToo', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsFileInSecondPath() {
		$this->fixture->loadRequiredTestClasses(
			array(
				t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
					'LoadMe.php',
				),
				t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Fixtures/' => array(
					'LoadMe.php',
				),
			)
		);

		$this->assertTrue(
			class_exists('Tx_Phpunit_Fixtures_LoadMe', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function createIconStyleForLoadedExtensionReturnsExtensionIcon() {
		$this->assertContains(
			'url(' . t3lib_extMgm::extRelPath('phpunit') . 'ext_icon.gif)',
			$this->fixture->createIconStyle('phpunit')
		);
	}

	/**
	 * @test
	 */
	public function createIconStyleForCoreReturnsTypo3Icon() {
		$testFinder = new Tx_Phpunit_Service_TestFinder();
		if (!$testFinder->hasCoreTests()) {
			$this->markTestSkipped('This test can only be run if the TYPO3 Core unit tests are present.');
		}

		$this->assertContains(
			'url(' . t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png)',
			$this->fixture->createIconStyle(Tx_Phpunit_Testable::CORE_KEY)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForNotLoadedExtensionThrowsException() {
		$this->fixture->createIconStyle('not_loaded_extension');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForEmptyExtensionKeyThrowsException() {
		$this->fixture->createIconStyle('');
	}

	/**
	 * @test
	 */
	public function isAcceptedTestSuitClassReturnsTrueForNonSpecialClass() {
		$this->assertTrue(
			$this->fixture->isAcceptedTestSuitClass('foo')
		);
	}

	/**
	 * @test
	 */
	public function isAcceptedTestSuitClassReturnsTrueForTestCaseSubClass() {
		$this->assertTrue(
			$this->fixture->isAcceptedTestSuitClass(get_class($this))
		);
	}

	/**
	 * @test
	 */
	public function isAcceptedTestSuitClassReturnsFalseForPhpunitTestCase() {
		$this->assertFalse(
			$this->fixture->isAcceptedTestSuitClass('Tx_Phpunit_TestCase')
		);
	}

	/**
	 * @test
	 */
	public function isAcceptedTestSuitClassReturnsFalseForPhpunitDatabaseTestCase() {
		$this->assertFalse(
			$this->fixture->isAcceptedTestSuitClass('Tx_Phpunit_Database_TestCase')
		);
	}

	/**
	 * @test
	 */
	public function isAcceptedTestSuitClassReturnsFalseForPhpunitSeleniumTestCase() {
		$this->assertFalse(
			$this->fixture->isAcceptedTestSuitClass('Tx_Phpunit_Selenium_TestCase')
		);
	}


	/*
	 * Tests concerning createExtensionSelector
	 */

	/**
	 * @test
	 */
	public function createExtensionSelectorCreatesForm() {
		$this->assertContains(
			'<form',
			$this->fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorCreatesOptionForExtensionWithTests() {
		$selectedExtension = 'aaa';

		$className = $this->createAccessibleProxy();
		/** @var $fixture Tx_Phpunit_BackEnd_Module */
		$fixture = new $className();

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$this->assertRegExp(
			'/<option[^>]*value="phpunit"/',
			$fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorContainsExtensionKeyOfExtensionWithTests() {
		$testable = new Tx_Phpunit_Testable();
		$testable->setKey('t3dd11');

		$testFinderMock = $this->getMock(
			'Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything')
		);
		$testFinderMock->expects($this->once())->method('getTestablesForEverything')
			->will($this->returnValue(array($testable)));

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('getTestFinder'));
		$fixture->injectUserSettingsService($this->userSettingsService);
		$fixture->expects($this->once())->method('getTestFinder')
			->will($this->returnValue($testFinderMock));

		$this->assertRegExp(
			'#<option [^>]*value="t3dd11">t3dd11</option>#',
			$fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorContainsIconPathKeyOfExtensionWithTests() {
		$this->assertContains(
			'background: url(../typo3conf/ext/phpunit/ext_icon.gif)',
			$this->fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorMarksSelectedExtensionAsSelected() {
		$this->userSettingsService->set('extSel', 'phpunit');

		$this->assertRegExp(
			'#<option [^>]* selected="selected">phpunit</option>#',
			$this->fixture->createExtensionSelector()
		);

	}

	/**
	 * @test
	 */
	public function createExtensionSelectorMarksNotSelectedExtensionAsNotSelected() {
		$this->userSettingsService->set('extSel', 't3dd11');

		$this->assertNotRegExp(
			'#<option [^>]*selected="selected">phpunit</option>#',
			$this->fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorContainsKeyForAllExtensions() {
		$this->assertRegExp(
			'/<option [^>]*value="uuall"/',
			$this->fixture->createExtensionSelector()
		);
	}

	/**
	 * @test
	 */
	public function createExtensionSelectorForAllSelectedMarksAllAsSelected() {
		$this->userSettingsService->set('extSel', Tx_Phpunit_Testable::ALL_EXTENSIONS);

		$this->assertRegExp(
			'/<option [^>]* value="' . Tx_Phpunit_Testable::ALL_EXTENSIONS . '" selected="selected">/',
			$this->fixture->createExtensionSelector()
		);

	}

	/**
	 * @test
	 */
	public function createExtensionSelectorForOtherExtensionSelectedMarksAllAsNotSelected() {
		$this->userSettingsService->set('extSel', 't3dd11');

		$this->assertNotRegExp(
			'/<option [^>]* value="uuall" selected="selected">/',
			$this->fixture->createExtensionSelector()
		);
	}


	/*
	 * Tests concerning createTestCaseSelector
	 */

	/**
	 * @test
	 */
	public function createTestCaseSelectorCreatesForm() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertContains(
			'<form',
			$this->fixture->createTestCaseSelector( $selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorForNoExtensionSelectedReturnsEmptyString() {
		$selectedExtension = '';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertSame(
			'',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorForAllExtensionSelectedReturnsEmptyString() {
		$selectedExtension = Tx_Phpunit_Testable::ALL_EXTENSIONS;
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertSame(
			'',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorCreatesOptionForExistingTestcaseFromSelectedExtension() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertRegExp(
			'/<option[^>]*value="Tx_Phpunit_BackEnd_ModuleTest"/',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorNotCreatesOptionForExistingTestcaseFromNotSelectedExtension() {
		if (!t3lib_extMgm::isLoaded('oelib')) {
			$this->markTestSkipped('This tests requires the "oelib" extension to be loaded.');
		}

		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertNotContains(
			'value="tx_oelib_DataMapperTest"',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorContainsIconPathKeyOfExtensionWithTests() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertContains(
			'background: url(../typo3conf/ext/phpunit/ext_icon.gif)',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorMarksSelectedTestCaseAsSelected() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);
		$_GET['testCaseFile'] = 'Tx_Phpunit_Service_TestFinderTest';

		$this->assertRegExp(
			'#<option [^>]* selected="selected">Tx_Phpunit_Service_TestFinderTest</option>#',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorMarksForOtherTestCaseSelectedTestCaseAsNotSelected() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);
		$_GET['testCaseFile'] = 'Tx_Phpunit_Service_TestFinderTest';

		$this->assertNotRegExp(
			'#<option [^>]* selected="selected">Tx_Phpunit_BackEnd_ModuleTest</option>#',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorMarksNoTestCaseSelectedTestCaseAsNotSelected() {
		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);
		$_GET['testCaseFile'] = '';

		$this->assertNotRegExp(
			'#<option [^>]* selected="selected">Tx_Phpunit_BackEnd_ModuleTest</option>#',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}
}
?>