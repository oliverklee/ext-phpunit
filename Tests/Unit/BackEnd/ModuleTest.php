<?php
namespace OliverKlee\Phpunit\Tests\Unit\BackEnd;

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

use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
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
class ModuleTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_BackEnd_Module
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $request = null;

    /**
     * @var \Tx_Phpunit_Service_FakeOutputService
     */
    protected $outputService = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $userSettingsService = null;

    /**
     * @var BackendUserAuthentication
     */
    private $backEndUserBackup = null;

    /**
     * @var \Tx_Phpunit_Service_TestFinder
     */
    protected $testFinder = null;

    /**
     * @var \Tx_Phpunit_Service_TestCaseService
     */
    protected $testCaseService = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $extensionSettingsService = null;

    /**
     * @var \Tx_Phpunit_ViewHelpers_ProgressBarViewHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progressBarViewHelper = null;

    /**
     * @var SingletonInterface[]
     */
    protected $singletonInstances = array();

    /**
     * @var DocumentTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentTemplate = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];

        $this->getLanguageService()->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xlf');

        if (!isset($GLOBALS['MCONF'])) {
            $GLOBALS['MCONF'] = array(
                'name' => $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'][1],
            );
        }
        $subjectClassName = $this->createAccessibleProxy();
        $this->subject = new $subjectClassName();

        $this->request = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectRequest($this->request);

        $this->outputService = new \Tx_Phpunit_Service_FakeOutputService();
        $this->subject->injectOutputService($this->outputService);

        $this->userSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectUserSettingsService($this->userSettingsService);

        $this->testFinder = new \Tx_Phpunit_Service_TestFinder();
        $this->extensionSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->testFinder->injectExtensionSettingsService($this->extensionSettingsService);
        $this->subject->injectTestFinder($this->testFinder);

        $this->testCaseService = new \Tx_Phpunit_Service_TestCaseService();
        $this->testCaseService->injectUserSettingsService($this->userSettingsService);
        $this->subject->injectTestCaseService($this->testCaseService);

        $this->progressBarViewHelper = $this->getMock('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper');
        GeneralUtility::addInstance('Tx_Phpunit_ViewHelpers_ProgressBarViewHelper', $this->progressBarViewHelper);

        $this->documentTemplate = $this->getMock('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', array('startPage'));
        GeneralUtility::addInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', $this->documentTemplate);

        $this->singletonInstances = GeneralUtility::getSingletonInstances();
    }

    protected function tearDown()
    {
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
    private function createAccessibleProxy()
    {
        $className = 'Tx_Phpunit_BackEnd_ModuleAccessibleProxy';
        if (!class_exists($className, false)) {
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
    public function createAccessibleProxyCreatesModuleSubclass()
    {
        $className = $this->createAccessibleProxy();

        self::assertInstanceOf(
            'Tx_Phpunit_BackEnd_Module',
            new $className()
        );
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /*
     * Unit tests
     */

    /**
     * @test
     */
    public function mainForNoAdminBackEndUserShowsAdminRightsNeeded()
    {
        $GLOBALS['BE_USER']->user['admin'] = false;

        $this->subject->main();

        self::assertContains(
            $this->getLanguageService()->getLL('admin_rights_needed'),
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function mainForNoAdminBackEndUserDoesNotRunTests()
    {
        $GLOBALS['BE_USER']->user['admin'] = false;

        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTests'));
        $subject->injectOutputService($this->outputService);

        $subject->expects(self::never())->method('renderRunTests');

        $subject->main();
    }

    /**
     * @test
     */
    public function mainForAdminBackEndUserRunsTests()
    {
        $GLOBALS['BE_USER']->user['admin'] = true;

        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('createOpenNewWindowLink', 'renderRunTests'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingsService($this->userSettingsService);
        $subject->expects(self::once())->method('renderRunTests');

        $subject->main();
    }

    /**
     * @test
     */
    public function renderRunTestsForEmptyCommandRendersIntro()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('renderRunTestsIntro');

        $this->request->set('command', '');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForEmptyCommandNotRunsTests()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::never())->method('renderRunningTest');

        $this->request->set('command', '');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForInvalidCommandRendersIntro()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('renderRunTestsIntro');

        $this->request->set('command', 'invalid');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForInvalidCommandNotRunsTests()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::never())->method('renderRunningTest');

        $this->request->set('command', 'invalid');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForRunAllTestsCommandRendersIntroAndTests()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('renderRunTestsIntro');
        $subject->expects(self::once())->method('renderRunningTest');

        $this->request->set('command', 'runalltests');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForRunTestCaseFileCommandRendersIntroAndTests()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('renderRunTestsIntro');
        $subject->expects(self::once())->method('renderRunningTest');

        $this->request->set('command', 'runTestCaseFile');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsForRunSingleTestCommandRendersIntroAndTests()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock($this->createAccessibleProxy(), array('renderRunTestsIntro', 'renderRunningTest'));
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('renderRunTestsIntro');
        $subject->expects(self::once())->method('renderRunningTest');

        $this->request->set('command', 'runsingletest');

        $subject->renderRunTests();
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForNoExtensionsWithTestSuitesShowsErrorMessage()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            $this->createAccessibleProxy(),
            array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
        );
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
        $testFinder->expects(self::any())->method('existsTestableForAnything')->will(self::returnValue(false));
        $subject->injectTestFinder($testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->renderRunTestsIntro();

        self::assertContains(
            $this->getLanguageService()->getLL('could_not_find_exts_with_tests'),
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForNoExtensionsWithTestSuitesNotRendersExtensionSelector()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            $this->createAccessibleProxy(),
            array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
        );
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder');
        $testFinder->expects(self::any())->method('existsTestableForAnything')->will(self::returnValue(false));
        $subject->injectTestFinder($testFinder);

        $this->userSettingsService->set('extSel', 'phpunit');
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::never())->method('createExtensionSelector');

        $subject->renderRunTestsIntro();
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForExistingExtensionsWithTestSuitesRendersExtensionSelector()
    {
        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
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

        $subject->expects(self::once())->method('createExtensionSelector');

        $subject->renderRunTestsIntro();
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForSelectedExtensionRendersTestCaseSelector()
    {
        $selectedExtension = 'phpunit';

        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            $this->createAccessibleProxy(),
            array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
        );
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestCaseSelector')
            ->with($selectedExtension)->will(self::returnValue('test case selector'));

        $subject->renderRunTestsIntro();

        self::assertContains(
            'test case selector',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForSelectedExtensionRendersTestSelector()
    {
        $selectedExtension = 'phpunit';

        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            $this->createAccessibleProxy(),
            array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
        );
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestSelector')
            ->with($selectedExtension)->will(self::returnValue('test selector'));

        $subject->renderRunTestsIntro();

        self::assertContains(
            'test selector',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderRunTestsIntroForSelectedExtensionRendersCheckboxes()
    {
        $selectedExtension = 'phpunit';

        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            $this->createAccessibleProxy(),
            array('createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector')
        );
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestSelector')
            ->with($selectedExtension)->will(self::returnValue('test selector'));

        $subject->renderRunTestsIntro();

        self::assertContains(
            'test selector',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function findTestCasesInDirReturnsEmptyArrayIfDirectoryDoesNotExist()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
        $notExistingDirectory = 'vfs://Foo/bar';

        self::assertSame(
            array(),
            $this->subject->findTestCasesInDir($notExistingDirectory)
        );
    }

    /**
     * @test
     */
    public function findTestCasesInDirCallsFindTestCasesInDirectoryOfTestCaseService()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
        $directory = 'vfs://Foo/';

        /** @var \Tx_Phpunit_Service_TestCaseService|\PHPUnit_Framework_MockObject_MockObject $testCaseService */
        $testCaseService = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('findTestCaseFilesInDirectory'));
        $testCaseService->expects(self::once())->method('findTestCaseFilesInDirectory')->with($directory);
        $this->subject->injectTestCaseService($testCaseService);

        $this->subject->findTestCasesInDir($directory);
    }

    /**
     * @test
     */
    public function findTestCasesInDirReturnsArrayWithFoundTestCaseFiles()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('Foo'));
        $directory = 'vfs://Foo/';
        $testFiles = array('class.testOneTest.php', 'class.testTwoTest.php');

        /** @var \Tx_Phpunit_Service_TestCaseService|\PHPUnit_Framework_MockObject_MockObject $testCaseService */
        $testCaseService = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('findTestCaseFilesInDirectory'));
        $testCaseService->expects(self::once())->method('findTestCaseFilesInDirectory')->will(self::returnValue($testFiles));
        $this->subject->injectTestCaseService($testCaseService);

        self::assertSame(
            array($directory => $testFiles),
            $this->subject->findTestCasesInDir($directory)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsFileInFirstPath()
    {
        $this->subject->loadRequiredTestClasses(
            array(
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
                    'LoadMe.php',
                ),
            )
        );

        self::assertTrue(
            class_exists('OliverKlee\\Phpunit\\Tests\\Unit\\BackEnd\\Fixtures\\LoadMe', false)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsSecondFileInFirstPath()
    {
        $this->subject->loadRequiredTestClasses(
            array(
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => array(
                    'LoadMe.php',
                    'LoadMeToo.php',
                ),
            )
        );

        self::assertTrue(
            class_exists('OliverKlee\\Phpunit\\Tests\\Unit\\BackEnd\\Fixtures\\LoadMeToo', false)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsFileInSecondPath()
    {
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

        self::assertTrue(
            class_exists('OliverKlee\\Phpunit\\Tests\\Unit\\Fixtures\\LoadMe', false)
        );
    }

    /**
     * @test
     */
    public function createIconStyleForLoadedExtensionReturnsExtensionIcon()
    {
        self::assertContains(
            'url(' . ExtensionManagementUtility::extRelPath('phpunit') . 'ext_icon.png)',
            $this->subject->createIconStyle('phpunit')
        );
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_NoTestsDirectory
     */
    public function createIconStyleForNotLoadedExtensionThrowsException()
    {
        $this->subject->createIconStyle('not_loaded_extension');
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_NoTestsDirectory
     */
    public function createIconStyleForEmptyExtensionKeyThrowsException()
    {
        $this->subject->createIconStyle('');
    }


    /*
     * Tests concerning createTestCaseSelector
     */

    /**
     * @test
     */
    public function createTestCaseSelectorCreatesForm()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertContains(
            '<form',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorForNoExtensionSelectedReturnsEmptyString()
    {
        $selectedExtension = '';
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertSame(
            '',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorForAllExtensionSelectedReturnsEmptyString()
    {
        $selectedExtension = \Tx_Phpunit_Testable::ALL_EXTENSIONS;
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertSame(
            '',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorCreatesOptionForExistingTestcaseFromSelectedExtension()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertRegExp(
            '/<option[^>]*value="OliverKlee\\\\Phpunit\\\\Tests\\\\Unit\\\\BackEnd\\\\ModuleTest"/',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorNotCreatesOptionForExistingTestcaseFromNotSelectedLoadedExtension()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertNotContains(
            'GeneralUtilityTest"',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorContainsIconPathKeyOfExtensionWithTests()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);

        self::assertContains(
            'background: url(' . ExtensionManagementUtility::extRelPath('phpunit') . 'ext_icon.png)',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorMarksSelectedTestCaseAsSelected()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);
        $this->request->set('testCaseFile', 'OliverKlee\\Phpunit\\Tests\\Unit\\Service\\TestFinderTest');

        self::assertRegExp(
            '#<option [^>]* selected="selected">OliverKlee\\\\Phpunit\\\Tests\\\Unit\\\Service\\\TestFinderTest</option>#',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorMarksForOtherTestCaseSelectedTestCaseAsNotSelected()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);
        $this->request->set('testCaseFile', 'Tx_Phpunit_Tests_Unit_Service_TestFinderTest');

        self::assertNotRegExp(
            '#<option [^>]* selected="selected">Tx_Phpunit_Tests_Unit_BackEnd_ModuleTest</option>#',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }

    /**
     * @test
     */
    public function createTestCaseSelectorMarksNoTestCaseSelectedTestCaseAsNotSelected()
    {
        $selectedExtension = 'phpunit';
        $this->userSettingsService->set('extSel', $selectedExtension);
        $this->request->set('testCaseFile', '');

        self::assertNotRegExp(
            '#<option [^>]* selected="selected">Tx_Phpunit_Tests_Unit_BackEnd_ModuleTest</option>#',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }
}