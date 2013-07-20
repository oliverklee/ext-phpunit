<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2013 Oliver Klee (typo3-coding@oliverklee.de)
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

if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
	require_once(PATH_typo3 . 'template.php');
}
$GLOBALS['LANG']->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xml');

/**
 * Test case.
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
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $request = NULL;

	/**
	 * @var Tx_Phpunit_Service_FakeOutputService
	 */
	protected $outputService = NULL;

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $userSettingsService = NULL;

	/**
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackup = NULL;

	/**
	 * @var Tx_Phpunit_Service_TestFinder
	 */
	protected $testFinder = NULL;

	/**
	 * @var Tx_Phpunit_Service_TestCaseService
	 */
	protected $testCaseService = NULL;

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $extensionSettingsService = NULL;

	/**
	 * @var Tx_Phpunit_ViewHelpers_ProgressBarViewHelper|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $progressBarViewHelper = NULL;

	/**
	 * @var bigDoc|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $bigDocumentTemplate = NULL;

	public function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];

		$fixtureClassName = $this->createAccessibleProxy();
		$this->fixture = new $fixtureClassName();

		$this->request = new Tx_Phpunit_TestingDataContainer();
		$this->fixture->injectRequest($this->request);

		$this->outputService = new Tx_Phpunit_Service_FakeOutputService();
		$this->fixture->injectOutputService($this->outputService);

		$this->userSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->fixture->injectUserSettingsService($this->userSettingsService);

		$this->testFinder = new Tx_Phpunit_Service_TestFinder();
		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->testFinder->injectExtensionSettingsService($this->extensionSettingsService);
		$this->fixture->injectTestFinder($this->testFinder);

		$this->testCaseService = new Tx_Phpunit_Service_TestCaseService();
		$this->testCaseService->injectUserSettingsService($this->userSettingsService);
		$this->fixture->injectTestCaseService($this->testCaseService);

		$this->progressBarViewHelper = $this->getMock('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper');
		t3lib_div::addInstance('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper', $this->progressBarViewHelper);

		$this->bigDocumentTemplate = $this->getMock('bigDoc', array('startPage'));
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6000000) {
			t3lib_div::addInstance('bigDoc', $this->bigDocumentTemplate);
		} else {
			t3lib_div::addInstance('TYPO3\\CMS\\Backend\\Template\\BigDocumentTemplate', $this->bigDocumentTemplate);
		}
	}

	public function tearDown() {
		$this->fixture->__destruct();

		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		t3lib_div::purgeInstances();

		unset(
			$this->fixture, $this->request, $this->outputService, $this->userSettingsService, $this->backEndUserBackup,
			$this->testFinder, $this->extensionSettingsService, $this->progressBarViewHelper,
			$this->bigDocumentTemplate, $this->testCaseService
		);
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
				'  public function renderRunTests() {' .
				'    parent::renderRunTests();' .
				'  }' .
				'  public function renderRunTestsIntro() {' .
				'    parent::renderRunTestsIntro();' .
				'  }' .
				'  public function createExtensionSelector() {' .
				'    parent::createExtensionSelector();' .
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
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTests'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectUserSettingsService($this->userSettingsService);
		$fixture->expects($this->once())->method('renderRunTests');

		$fixture->main();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForEmptyCommandRendersIntro() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('renderRunTestsIntro');

		$this->request->set('command', '');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForEmptyCommandNotRunsTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->never())->method('renderRunningTest');

		$this->request->set('command', '');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForInvalidCommandRendersIntro() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('renderRunTestsIntro');

		$this->request->set('command', 'invalid');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForInvalidCommandNotRunsTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->never())->method('renderRunningTest');

		$this->request->set('command', 'invalid');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunAllTestsCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('renderRunTestsIntro');
		$fixture->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runalltests');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunTestCaseFileCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('renderRunTestsIntro');
		$fixture->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runTestCaseFile');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunSingleTestCommandRendersIntroAndTests() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('renderRunTestsIntro');
		$fixture->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runsingletest');

		$fixture->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForNoExtensionsWithTestSuitesShowsErrorMessage() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')->will($this->returnValue(FALSE));
		$fixture->injectTestFinder($testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->renderRunTestsIntro();

		$this->assertContains(
			$GLOBALS['LANG']->getLL('could_not_find_exts_with_tests'),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForNoExtensionsWithTestSuitesNotRendersExtensionSelector() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')->will($this->returnValue(FALSE));
		$fixture->injectTestFinder($testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->never())->method('createExtensionSelector');

		$fixture->renderRunTestsIntro();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForExistingExtensionsWithTestSuitesRendersExtensionSelector() {
		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$fixture->injectUserSettingsService($this->userSettingsService);
		$fixture->injectTestFinder($this->testFinder);
		$fixture->injectTestCaseService($this->testCaseService);

		$fixture->expects($this->once())->method('createExtensionSelector');

		$fixture->renderRunTestsIntro();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForSelectedExtensionRendersTestCaseSelector() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestCaseSelector')
			->with($selectedExtension)->will($this->returnValue('test case selector'));

		$fixture->renderRunTestsIntro();

		$this->assertContains(
			'test case selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForSelectedExtensionRendersTestSelector() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$fixture->renderRunTestsIntro();

		$this->assertContains(
			'test selector',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForSelectedExtensionRendersCheckboxes() {
		$selectedExtension = 'phpunit';

		/** @var $fixture Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$fixture = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$fixture->injectRequest($this->request);
		$fixture->injectOutputService($this->outputService);
		$fixture->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$fixture->injectUserSettingsService($this->userSettingsService);

		$fixture->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$fixture->renderRunTestsIntro();

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
	public function findTestCasesInDirCallsFindTestCasesInDirectoryOfTestCaseService() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
		$directory = 'vfs://Foo/';

		/** @var $testCaseService Tx_Phpunit_Service_TestCaseService|PHPUnit_Framework_MockObject_MockObject */
		$testCaseService = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('findTestCaseFilesInDirectory'));
		$testCaseService->expects($this->once())->method('findTestCaseFilesInDirectory')->with($directory);
		$this->fixture->injectTestCaseService($testCaseService);

		$this->fixture->findTestCasesInDir($directory);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirReturnsArrayWithFoundTestCaseFiles() {
		vfsStreamWrapper::register();
		vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
		$directory = 'vfs://Foo/';
		$testFiles = array('class.testOneTest.php', 'class.testTwoTest.php');

		/** @var $testCaseService Tx_Phpunit_Service_TestCaseService|PHPUnit_Framework_MockObject_MockObject */
		$testCaseService = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('findTestCaseFilesInDirectory'));
		$testCaseService->expects($this->once())->method('findTestCaseFilesInDirectory')->will($this->returnValue($testFiles));
		$this->fixture->injectTestCaseService($testCaseService);

		$this->assertSame(
			array($directory => $testFiles),
			$this->fixture->findTestCasesInDir($directory)
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
			class_exists('Tx_Phpunit_Tests_BackEnd_Fixtures_LoadMe', FALSE)
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
			class_exists('Tx_Phpunit_Tests_BackEnd_Fixtures_LoadMeToo', FALSE)
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
			class_exists('Tx_Phpunit_Tests_Fixtures_LoadMe', FALSE)
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
		$this->request->set('testCaseFile', 'Tx_Phpunit_Service_TestFinderTest');

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
		$this->request->set('testCaseFile', 'Tx_Phpunit_Service_TestFinderTest');

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
		$this->request->set('testCaseFile', '');

		$this->assertNotRegExp(
			'#<option [^>]* selected="selected">Tx_Phpunit_BackEnd_ModuleTest</option>#',
			$this->fixture->createTestCaseSelector($selectedExtension)
		);
	}
}
?>
