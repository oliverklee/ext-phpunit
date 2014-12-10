<?php
/*
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

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

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
	protected $subject = NULL;

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
	 * @var BackendUserAuthentication
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
	 * @var SingletonInterface[]
	 */
	protected $singletonInstances = array();

	/**
	 * @var DocumentTemplate|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $documentTemplate = NULL;

	protected function setUp() {
		$this->backEndUserBackup = $GLOBALS['BE_USER'];

		$this->getLanguageService()->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xml');

		$subjectClassName = $this->createAccessibleProxy();
		$this->subject = new $subjectClassName();

		$this->request = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectRequest($this->request);

		$this->outputService = new Tx_Phpunit_Service_FakeOutputService();
		$this->subject->injectOutputService($this->outputService);

		$this->userSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectUserSettingsService($this->userSettingsService);

		$this->testFinder = new Tx_Phpunit_Service_TestFinder();
		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->testFinder->injectExtensionSettingsService($this->extensionSettingsService);
		$this->subject->injectTestFinder($this->testFinder);

		$this->testCaseService = new Tx_Phpunit_Service_TestCaseService();
		$this->testCaseService->injectUserSettingsService($this->userSettingsService);
		$this->subject->injectTestCaseService($this->testCaseService);

		$this->progressBarViewHelper = $this->getMock('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper');
		GeneralUtility::addInstance('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper', $this->progressBarViewHelper);

		$this->documentTemplate = $this->getMock('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', array('startPage'));
		GeneralUtility::addInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', $this->documentTemplate);

		$this->singletonInstances = GeneralUtility::getSingletonInstances();
	}

	protected function tearDown() {
		$GLOBALS['BE_USER'] = $this->backEndUserBackup;

		GeneralUtility::purgeInstances();
		GeneralUtility::resetSingletonInstances($this->singletonInstances);
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

	/**
	 * Returns $GLOBALS['LANG'].
	 *
	 * @return LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function mainForNoAdminBackEndUserShowsAdminRightsNeeded() {
		$GLOBALS['BE_USER']->user['admin'] = FALSE;

		$this->subject->main();

		$this->assertContains(
			$this->getLanguageService()->getLL('admin_rights_needed'),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function mainForAdminBackEndUserRunsTests() {
		$GLOBALS['BE_USER']->user['admin'] = TRUE;

		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTests'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingsService($this->userSettingsService);
		$subject->expects($this->once())->method('renderRunTests');

		$subject->main();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForEmptyCommandRendersIntro() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('renderRunTestsIntro');

		$this->request->set('command', '');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForEmptyCommandNotRunsTests() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->never())->method('renderRunningTest');

		$this->request->set('command', '');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForInvalidCommandRendersIntro() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('renderRunTestsIntro');

		$this->request->set('command', 'invalid');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForInvalidCommandNotRunsTests() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->never())->method('renderRunningTest');

		$this->request->set('command', 'invalid');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunAllTestsCommandRendersIntroAndTests() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('renderRunTestsIntro');
		$subject->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runalltests');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunTestCaseFileCommandRendersIntroAndTests() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('renderRunTestsIntro');
		$subject->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runTestCaseFile');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsForRunSingleTestCommandRendersIntroAndTests() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('renderRunTestsIntro');
		$subject->expects($this->once())->method('renderRunningTest');

		$this->request->set('command', 'runsingletest');

		$subject->renderRunTests();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForNoExtensionsWithTestSuitesShowsErrorMessage() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')->will($this->returnValue(FALSE));
		$subject->injectTestFinder($testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->renderRunTestsIntro();

		$this->assertContains(
			$this->getLanguageService()->getLL('could_not_find_exts_with_tests'),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForNoExtensionsWithTestSuitesNotRendersExtensionSelector() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
		$testFinder->expects($this->any())->method('existsTestableForAnything')->will($this->returnValue(FALSE));
		$subject->injectTestFinder($testFinder);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->never())->method('createExtensionSelector');

		$subject->renderRunTestsIntro();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForExistingExtensionsWithTestSuitesRendersExtensionSelector() {
		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);

		$this->userSettingsService->set('extSel', 'phpunit');
		$subject->injectUserSettingsService($this->userSettingsService);
		$subject->injectTestFinder($this->testFinder);
		$subject->injectTestCaseService($this->testCaseService);

		$subject->expects($this->once())->method('createExtensionSelector');

		$subject->renderRunTestsIntro();
	}

	/**
	 * @test
	 */
	public function renderRunTestsIntroForSelectedExtensionRendersTestCaseSelector() {
		$selectedExtension = 'phpunit';

		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('createTestCaseSelector')
			->with($selectedExtension)->will($this->returnValue('test case selector'));

		$subject->renderRunTestsIntro();

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

		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$subject->renderRunTestsIntro();

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

		/** @var $subject Tx_Phpunit_BackEnd_Module|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock(
			$this->createAccessibleProxy(),
			array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
		);
		$subject->injectRequest($this->request);
		$subject->injectOutputService($this->outputService);
		$subject->injectTestFinder($this->testFinder);

		$this->userSettingsService->set('extSel', $selectedExtension);
		$subject->injectUserSettingsService($this->userSettingsService);

		$subject->expects($this->once())->method('createTestSelector')
			->with($selectedExtension)->will($this->returnValue('test selector'));

		$subject->renderRunTestsIntro();

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
			$this->subject->findTestCasesInDir($notExistingDirectory)
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
		$this->subject->injectTestCaseService($testCaseService);

		$this->subject->findTestCasesInDir($directory);
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
		$this->subject->injectTestCaseService($testCaseService);

		$this->assertSame(
			array($directory => $testFiles),
			$this->subject->findTestCasesInDir($directory)
		);
	}

	/**
	 * @test
	 */
	public function loadRequiredTestClassesLoadsFileInFirstPath() {
		$this->subject->loadRequiredTestClasses(
			array(
				ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
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
		$this->subject->loadRequiredTestClasses(
			array(
				ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
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
		$this->subject->loadRequiredTestClasses(
			array(
				ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
					'LoadMe.php',
				),
				ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Fixtures/' => array(
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
			'url(' . ExtensionManagementUtility::extRelPath('phpunit') . 'ext_icon.gif)',
			$this->subject->createIconStyle('phpunit')
		);
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForNotLoadedExtensionThrowsException() {
		$this->subject->createIconStyle('not_loaded_extension');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function createIconStyleForEmptyExtensionKeyThrowsException() {
		$this->subject->createIconStyle('');
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
			$this->subject->createTestCaseSelector( $selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
		);
	}

	/**
	 * @test
	 */
	public function createTestCaseSelectorNotCreatesOptionForExistingTestcaseFromNotSelectedExtension() {
		if (!ExtensionManagementUtility::isLoaded('oelib')) {
			$this->markTestSkipped('This tests requires the "oelib" extension to be loaded.');
		}

		$selectedExtension = 'phpunit';
		$this->userSettingsService->set('extSel', $selectedExtension);

		$this->assertNotContains(
			'value="tx_oelib_DataMapperTest"',
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
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
			$this->subject->createTestCaseSelector($selectedExtension)
		);
	}
}