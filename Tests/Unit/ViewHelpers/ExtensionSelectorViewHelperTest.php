<?php
namespace OliverKlee\Phpunit\Tests\Unit\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
class ExtensionSelectorViewHelperTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_Service_ExtensionSettingsService
     */
    protected $extensionSettingsService = null;

    /**
     * @var \Tx_Phpunit_Service_FakeOutputService
     */
    protected $outputService = null;

    /**
     * @var \Tx_Phpunit_Service_TestFinder
     */
    protected $testFinder = null;

    /**
     * @var \Tx_Phpunit_Service_UserSettingsService
     */
    protected $userSettingsService = null;

    /**
     * @var LanguageService
     */
    protected $languageServiceBackup = null;

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        if (!empty($GLOBALS['LANG'])) {
            $this->languageServiceBackup = $GLOBALS['LANG'];
        }

        /** @var LanguageService|\PHPUnit_Framework_MockObject_MockObject $languageServiceMock */
        $languageServiceMock = $this->getMock(LanguageService::class);
        $languageServiceMock->expects($this->any())->method('getLL')->willReturn('translatedLabel');
        $GLOBALS['LANG'] = $languageServiceMock;

        $this->subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();

        $this->outputService = new \Tx_Phpunit_Service_FakeOutputService();
        $this->subject->injectOutputService($this->outputService);

        $this->extensionSettingsService = new \Tx_Phpunit_TestingDataContainer();

        $this->testFinder = new \Tx_Phpunit_Service_TestFinder();
        $this->testFinder->injectExtensionSettingsService($this->extensionSettingsService);
        $this->subject->injectTestFinder($this->testFinder);

        $this->userSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectUserSettingService($this->userSettingsService);
    }

    protected function tearDown()
    {
        if (!empty($this->languageServiceBackup)) {
            $GLOBALS['LANG'] = $this->languageServiceBackup;
        }
    }

    /**
     * @test
     */
    public function classIsSubclassOfAbstractSelectorViewHelper()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_ViewHelpers_AbstractSelectorViewHelper',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function renderCreatesFormTag()
    {
        $this->subject->render();

        self::assertRegExp(
            '/<form[^>]*>/',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesSelectTag()
    {
        $this->subject->render();

        self::assertRegExp(
            '/<select[^>]*>/',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesButtonTag()
    {
        $this->subject->render();

        self::assertRegExp(
            '/<button[^>]*>/',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesOptionTagForAllExtensions()
    {
        $this->subject->render();

        self::assertRegExp(
            '/<option class="alltests" value="' . \Tx_Phpunit_Testable::ALL_EXTENSIONS . '"[^>]*>/',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderSelectsOptionTagForAllExtensions()
    {
        $this->userSettingsService->set(
            \Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE,
            \Tx_Phpunit_Testable::ALL_EXTENSIONS
        );
        $this->subject->injectUserSettingService($this->userSettingsService);
        $this->subject->render();

        self::assertContains(
            '<option class="alltests" value="' . \Tx_Phpunit_Testable::ALL_EXTENSIONS . '" selected="selected">',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesOneOptionTagWithoutExtensions()
    {
        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')->will(self::returnValue([]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertSame(
            1,
            substr_count($this->outputService->getCollectedOutput(), '<option')
        );
    }

    /**
     * @test
     */
    public function renderCreatesTwoOptionTagsWithOneExtension()
    {
        $extensionKey = 'phpunit';
        $testable = new \Tx_Phpunit_Testable();
        $testable->setKey($extensionKey);
        $testable->setIconPath(ExtensionManagementUtility::extRelPath($extensionKey) . 'ext_icon.gif');

        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([$extensionKey => $testable]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertSame(
            2,
            substr_count($this->outputService->getCollectedOutput(), '<option')
        );
    }

    /**
     * @test
     */
    public function renderCreatesOptionTagsForExtension()
    {
        $extensionKey = 'phpunit';
        $testable = new \Tx_Phpunit_Testable();
        $testable->setKey($extensionKey);
        $testable->setIconPath(ExtensionManagementUtility::extRelPath($extensionKey) . 'ext_icon.gif');

        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([$extensionKey => $testable]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertRegExp(
            '#<option[^>]*value="phpunit"[^>]*>phpunit</option>#',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesThreeOptionTagsWithTwoExtensions()
    {
        $extensionKey1 = 'phpunit';
        $testable1 = new \Tx_Phpunit_Testable();
        $testable1->setKey($extensionKey1);
        $testable1->setIconPath(ExtensionManagementUtility::extRelPath($extensionKey1) . 'ext_icon.gif');

        $extensionKey2 = 'core';
        $testable2 = new \Tx_Phpunit_Testable();
        $testable2->setKey($extensionKey2);
        $testable1->setIconPath(ExtensionManagementUtility::extRelPath($extensionKey2) . 'ext_icon.gif');

        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([$extensionKey1 => $testable1, $extensionKey2 => $testable2]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertSame(
            3,
            substr_count($this->outputService->getCollectedOutput(), '<option')
        );
    }

    /**
     * @test
     */
    public function renderCreatesExtensionKeyWithHtmlSpecialChars()
    {
        $extensionKey = '"php&unit"';
        $testable = new \Tx_Phpunit_Testable();
        $testable->setKey($extensionKey);
        $testable->setIconPath(ExtensionManagementUtility::extPath('phpunit') . 'ext_icon.gif');

        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([$extensionKey => $testable]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertContains(
            htmlspecialchars($extensionKey),
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderCreatesIconPathWithHtmlSpecialChars()
    {
        $extensionKey = 'phpunit';
        $testable = new \Tx_Phpunit_Testable();
        $testable->setKey($extensionKey);
        $testable->setIconPath(ExtensionManagementUtility::extPath('phpunit') . 'ext_&_icon.gif');

        $subject = new \Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
        $subject->injectOutputService($this->outputService);
        $subject->injectUserSettingService($this->userSettingsService);

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([$extensionKey => $testable]));
        $subject->injectTestFinder($testFinder);

        $subject->render();

        self::assertContains(
            htmlspecialchars($testable->getIconPath()),
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderNotSelectsAnyOptionWithoutSelectedExtension()
    {
        $this->userSettingsService->set(\Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, '');
        $this->subject->injectUserSettingService($this->userSettingsService);
        $this->subject->render();

        self::assertNotContains(
            ' selected="selected"',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderSelectsOptionForSelectedExtension()
    {
        $this->userSettingsService->set(\Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, 'phpunit');
        $this->subject->injectUserSettingService($this->userSettingsService);
        $this->subject->render();

        self::assertRegExp(
            '#<option[^>]* selected="selected">phpunit</option>#',
            $this->outputService->getCollectedOutput()
        );
    }

    /**
     * @test
     */
    public function renderNotSelectsAnyOptionWithInvalidSelectedExtension()
    {
        $this->userSettingsService->set(\Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, 'foo');
        $this->subject->injectUserSettingService($this->userSettingsService);

        $this->subject->render();

        self::assertNotContains(
            ' selected="selected"',
            $this->outputService->getCollectedOutput()
        );
    }
}
