<?php

namespace OliverKlee\PhpUnit\Tests\Unit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\PhpUnit\Tests\Unit\BackEnd\Fixtures\LoadMe;
use OliverKlee\PhpUnit\Tests\Unit\BackEnd\Fixtures\LoadMeToo;
use OliverKlee\PhpUnit\Tests\Unit\Service\TestFinderTest;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ModuleTest extends TestCase
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
    protected $singletonInstances = [];

    /**
     * @var DocumentTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentTemplate = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $this->getLanguageService()->includeLLFile('EXT:phpunit/Resources/Private/Language/locallang_backend.xlf');

        if (!isset($GLOBALS['MCONF'])) {
            $GLOBALS['MCONF'] = [
                'name' => $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['phpunit'][1],
            ];
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

        $this->progressBarViewHelper = $this->createMock(\Tx_Phpunit_ViewHelpers_ProgressBarViewHelper::class);
        GeneralUtility::addInstance(\Tx_Phpunit_ViewHelpers_ProgressBarViewHelper::class, $this->progressBarViewHelper);

        $this->documentTemplate = $this->getMockBuilder(DocumentTemplate::class)->setMethods(['startPage'])->getMock();
        GeneralUtility::addInstance(DocumentTemplate::class, $this->documentTemplate);

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
     * Creates a subclass \Tx_Phpunit_BackEnd_Module with the protected functions
     * made public.
     *
     * @return string the name of the accessible proxy class
     */
    private function createAccessibleProxy()
    {
        $className = 'Tx_Phpunit_BackEnd_ModuleAccessibleProxy';
        if (!class_exists($className, false)) {
            eval(
                'class ' . $className . ' extends \\Tx_Phpunit_BackEnd_Module {' .
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

        /** @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Phpunit_BackEnd_Module::class)->setMethods(['renderRunTests'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunningTest', 'renderRunTests'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['renderRunTestsIntro', 'renderRunningTest'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(
                ['createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector']
            )->getMock();
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->createMock(\Tx_Phpunit_Service_TestFinder::class);
        $testFinder->method('existsTestableForAnything')->willReturn(false);
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(
                ['createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector']
            )->getMock();
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->createMock(\Tx_Phpunit_Service_TestFinder::class);
        $testFinder->method('existsTestableForAnything')->willReturn(false);
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(['createExtensionSelector'])->getMock();
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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(
                ['createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector']
            )->getMock();
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestCaseSelector')
            ->with($selectedExtension)->willReturn('test case selector');

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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(
                ['createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector']
            )->getMock();
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestSelector')
            ->with($selectedExtension)->willReturn('test selector');

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
        $subject = $this->getMockBuilder($this->createAccessibleProxy())
            ->setMethods(
                ['createExtensionSelector', 'createTestCaseSelector', 'createCheckboxes', 'createTestSelector']
            )->getMock();
        $subject->injectRequest($this->request);
        $subject->injectOutputService($this->outputService);
        $subject->injectTestFinder($this->testFinder);

        $this->userSettingsService->set('extSel', $selectedExtension);
        $subject->injectUserSettingsService($this->userSettingsService);

        $subject->expects(self::once())->method('createTestSelector')
            ->with($selectedExtension)->willReturn('test selector');

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
            [],
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
        $testCaseService = $this->getMockBuilder(\Tx_Phpunit_Service_TestCaseService::class)
            ->setMethods(['findTestCaseFilesInDirectory'])->getMock();
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
        $testFiles = ['class.testOneTest.php', 'class.testTwoTest.php'];

        /** @var \Tx_Phpunit_Service_TestCaseService|\PHPUnit_Framework_MockObject_MockObject $testCaseService */
        $testCaseService = $this->getMockBuilder(\Tx_Phpunit_Service_TestCaseService::class)
            ->setMethods(['findTestCaseFilesInDirectory'])->getMock();
        $testCaseService->expects(self::once())
            ->method('findTestCaseFilesInDirectory')
            ->willReturn($testFiles);
        $this->subject->injectTestCaseService($testCaseService);

        self::assertSame(
            [$directory => $testFiles],
            $this->subject->findTestCasesInDir($directory)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsFileInFirstPath()
    {
        $this->subject->loadRequiredTestClasses(
            [
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => [
                    'LoadMe.php',
                ],
            ]
        );

        self::assertTrue(
            class_exists(LoadMe::class, false)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsSecondFileInFirstPath()
    {
        $this->subject->loadRequiredTestClasses(
            [
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => [
                    'LoadMe.php',
                    'LoadMeToo.php',
                ],
            ]
        );

        self::assertTrue(
            class_exists(LoadMeToo::class, false)
        );
    }

    /**
     * @test
     */
    public function loadRequiredTestClassesLoadsFileInSecondPath()
    {
        $this->subject->loadRequiredTestClasses(
            [
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/BackEnd/Fixtures/' => [
                    'LoadMe.php',
                ],
                ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Fixtures/' => [
                    'LoadMe.php',
                ],
            ]
        );

        self::assertTrue(
            class_exists(LoadMe::class, false)
        );
    }

    /**
     * @test
     */
    public function createIconStyleForLoadedExtensionReturnsExtensionIcon()
    {
        self::assertContains(
            'url(' . PathUtility::getAbsoluteWebPath('../typo3conf/ext/phpunit/ext_icon.png') . ')',
            $this->subject->createIconStyle('phpunit')
        );
    }

    /**
     * @test
     */
    public function createIconStyleForNotLoadedExtensionThrowsException()
    {
        $this->expectException(\Tx_Phpunit_Exception_NoTestsDirectory::class);

        $this->subject->createIconStyle('not_loaded_extension');
    }

    /**
     * @test
     */
    public function createIconStyleForEmptyExtensionKeyThrowsException()
    {
        $this->expectException(\Tx_Phpunit_Exception_NoTestsDirectory::class);

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
            '/<option[^>]*value="OliverKlee\\\\PhpUnit\\\\Tests\\\\Unit\\\\BackEnd\\\\ModuleTest"/',
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
            'background: url(' . PathUtility::getAbsoluteWebPath('../typo3conf/ext/phpunit/ext_icon.png') . ')',
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
        $this->request->set('testCaseFile', TestFinderTest::class);

        self::assertRegExp(
            '#<option [^>]* selected="selected">OliverKlee\\\\PhpUnit\\\\Tests\\\\Unit\\\\Service\\\\TestFinderTest</option>#',
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
        $this->request->set('testCaseFile', TestFinderTest::class);

        self::assertNotRegExp(
            '#<option [^>]* selected="selected">OliverKlee\\\\PhpUnit\\\\Tests\\\\Unit\\\\BackEnd\\\\ModuleTest</option>#',
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
            '#<option [^>]* selected="selected">OliverKlee\\\\PhpUnit\\\\Tests\\\\Unit\\\\BackEnd\\\\ModuleTest</option>#',
            $this->subject->createTestCaseSelector($selectedExtension)
        );
    }
}
