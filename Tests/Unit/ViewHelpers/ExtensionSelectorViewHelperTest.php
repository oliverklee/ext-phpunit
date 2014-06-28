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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
class Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelperTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Phpunit_Service_ExtensionSettingsService
	 */
	protected $extensionSettingsService = NULL;

	/**
	 * @var Tx_Phpunit_Service_FakeOutputService
	 */
	protected $outputService = NULL;

	/**
	 * @var Tx_Phpunit_Service_TestFinder
	 */
	protected $testFinder = NULL;

	/**
	 * @var Tx_Phpunit_Service_UserSettingsService
	 */
	protected $userSettingsService = NULL;

	public function setUp() {
		$this->subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();

		$this->outputService = new Tx_Phpunit_Service_FakeOutputService();
		$this->subject->injectOutputService($this->outputService);

		$this->extensionSettingsService = new Tx_Phpunit_TestingDataContainer();

		$this->testFinder = new Tx_Phpunit_Service_TestFinder();
		$this->testFinder->injectExtensionSettingsService($this->extensionSettingsService);
		$this->subject->injectTestFinder($this->testFinder);

		$this->userSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectUserSettingService($this->userSettingsService);
	}

	public function tearDown() {
		unset($this->subject, $this->outputService, $this->testFinder, $this->userSettingsService, $this->extensionSettingsService);
	}

	/**
	 * @test
	 */
	public function classIsSubclassOfAbstractSelectorViewHelper() {
		$this->assertInstanceOf(
			'Tx_Phpunit_ViewHelpers_AbstractSelectorViewHelper',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesFormTag() {
		$this->subject->render();

		$this->assertRegExp(
			'/<form[^>]*>/',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesSelectTag() {
		$this->subject->render();

		$this->assertRegExp(
			'/<select[^>]*>/',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesButtonTag() {
		$this->subject->render();

		$this->assertRegExp(
			'/<button[^>]*>/',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesOptionTagForAllExtensions() {
		$this->subject->render();

		$this->assertRegExp(
			'/<option class="alltests" value="uuall"[^>]*>/',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderSelectsOptionTagForAllExtensions() {
		$this->userSettingsService->set(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, Tx_Phpunit_Testable::ALL_EXTENSIONS);
		$this->subject->injectUserSettingService($this->userSettingsService);
		$this->subject->render();

		$this->assertRegExp(
			'/<option class="alltests" value="uuall"[^>]* selected="selected">/',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesOneOptionTagWithoutExtensions() {
		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')->will($this->returnValue(array()));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertSame(
			1,
			substr_count($this->outputService->getCollectedOutput(), '<option')
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesTwoOptionTagsWithOneExtension() {
		$extensionKey = 'phpunit';
		$testable = new Tx_Phpunit_Testable();
		$testable->setKey($extensionKey);
		$testable->setIconPath(t3lib_extMgm::extRelPath($extensionKey) . 'ext_icon.gif');

		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array($extensionKey => $testable)));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertSame(
			2,
			substr_count($this->outputService->getCollectedOutput(), '<option')
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesOptionTagsForExtension() {
		$extensionKey = 'phpunit';
		$testable = new Tx_Phpunit_Testable();
		$testable->setKey($extensionKey);
		$testable->setIconPath(t3lib_extMgm::extRelPath($extensionKey) . 'ext_icon.gif');

		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array($extensionKey => $testable)));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertRegExp(
			'#<option[^>]*value="phpunit"[^>]*>phpunit</option>#',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesThreeOptionTagsWithTwoExtensions() {
		$extensionKey1 = 'phpunit';
		$testable1 = new Tx_Phpunit_Testable();
		$testable1->setKey($extensionKey1);
		$testable1->setIconPath(t3lib_extMgm::extRelPath($extensionKey1) . 'ext_icon.gif');

		$extensionKey2 = 'cms';
		$testable2 = new Tx_Phpunit_Testable();
		$testable2->setKey($extensionKey2);
		$testable1->setIconPath(t3lib_extMgm::extRelPath($extensionKey2) . 'ext_icon.gif');

		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array($extensionKey1 => $testable1, $extensionKey2 => $testable2)));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertSame(
			3,
			substr_count($this->outputService->getCollectedOutput(), '<option')
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesExtensionKeyWithHtmlSpecialChars() {
		$extensionKey = '"php&unit"';
		$testable = new Tx_Phpunit_Testable();
		$testable->setKey($extensionKey);
		$testable->setIconPath('typo3conf/ext/phpunit/ext_icon.gif');

		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array($extensionKey => $testable)));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertContains(
			htmlspecialchars($extensionKey),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesIconPathWithHtmlSpecialChars() {
		$extensionKey = 'phpunit';
		$testable = new Tx_Phpunit_Testable();
		$testable->setKey($extensionKey);
		$testable->setIconPath('typo3conf/ext/phpunit/ext_&_icon.gif');

		$subject = new Tx_Phpunit_ViewHelpers_ExtensionSelectorViewHelper();
		$subject->injectOutputService($this->outputService);
		$subject->injectUserSettingService($this->userSettingsService);

		/** @var $testFinder Tx_Phpunit_Service_TestFinder|PHPUnit_Framework_MockObject_MockObject */
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestablesForEverything'));
		$testFinder->expects($this->any())->method('getTestablesForEverything')
			->will($this->returnValue(array($extensionKey => $testable)));
		$subject->injectTestFinder($testFinder);

		$subject->render();

		$this->assertContains(
			htmlspecialchars($testable->getIconPath()),
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderNotSelectsAnyOptionWithoutSelectedExtension() {
		$this->userSettingsService->set(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, '');
		$this->subject->injectUserSettingService($this->userSettingsService);
		$this->subject->render();

		$this->assertNotContains(
			' selected="selected"',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderSelectsOptionForSelectedExtension() {
		$this->userSettingsService->set(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, 'phpunit');
		$this->subject->injectUserSettingService($this->userSettingsService);
		$this->subject->render();

		$this->assertRegExp(
			'#<option[^>]* selected="selected">phpunit</option>#',
			$this->outputService->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function renderNotSelectsAnyOptionWithInvalidSelectedExtension() {
		$this->userSettingsService->set(Tx_Phpunit_Interface_Request::PARAMETER_KEY_TESTABLE, 'foo');
		$this->subject->injectUserSettingService($this->userSettingsService);

		$this->subject->render();

		$this->assertNotContains(
			' selected="selected"',
			$this->outputService->getCollectedOutput()
		);
	}
}