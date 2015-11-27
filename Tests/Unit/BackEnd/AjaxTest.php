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
use TYPO3\CMS\Core\Http\AjaxRequestHandler;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Tests_Unit_BackEnd_AjaxTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_Ajax
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $userSettingsService = NULL;

	/**
	 * backup of $_POST
	 *
	 * @var array
	 */
	private $postBackup = array();

	protected function setUp() {
		$this->postBackup = $GLOBALS['_POST'];
		$GLOBALS['_POST'] = array();

		$this->subject = new Tx_Phpunit_BackEnd_Ajax(FALSE);

		$this->userSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectUserSettingsService($this->userSettingsService);
	}

	protected function tearDown() {
		$GLOBALS['_POST'] = $this->postBackup;
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAndStateTrueSavesTrueStateToUserSettings() {
		$GLOBALS['_POST']['checkbox'] = 'failure';
		$GLOBALS['_POST']['state'] = '1';

		/** @var $ajax AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject  */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$this->subject->ajaxBroker(array(), $ajax);

		self::assertTrue(
			$this->userSettingsService->getAsBoolean('failure')
		);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAndMissingStateSavesFalseStateToUserSettings() {
		$GLOBALS['_POST']['checkbox'] = 'failure';

		/** @var $ajax AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject  */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$this->subject->ajaxBroker(array(), $ajax);

		self::assertFalse(
			$this->userSettingsService->getAsBoolean('failure')
		);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'failure';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForSuccessCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'success';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForErrorCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'error';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForSkippedCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'skipped';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForIncompleteCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'incomplete';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForTestDoxCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'testdox';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForCodeCoverageCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'codeCoverage';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForShowMemoryAndTimeCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'showMemoryAndTime';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForRunSeleniumTestsCheckboxParameterAddsSuccessContent() {
		$GLOBALS['_POST']['checkbox'] = 'runSeleniumTests';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('addContent')->with('success', TRUE);
		$ajax->expects(self::never())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForMissingCheckboxParameterSetsError() {
		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForInvalidCheckboxParameterSetsError() {
		$GLOBALS['_POST']['checkbox'] = 'anything else';

		/** @var AjaxRequestHandler|PHPUnit_Framework_MockObject_MockObject $ajax */
		$ajax = $this->getMock('TYPO3\\CMS\\Core\\Http\\AjaxRequestHandler', array(), array(''));
		$ajax->expects(self::once())->method('setError');

		$this->subject->ajaxBroker(array(), $ajax);
	}
}