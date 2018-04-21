<?php
namespace OliverKlee\Phpunit\Tests\Unit\BackEnd;

use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AjaxTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_BackEnd_Ajax
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $userSettingsService = null;

    /**
     * backup of $_POST
     *
     * @var array
     */
    private $postBackup = [];

    protected function setUp()
    {
        $this->postBackup = $GLOBALS['_POST'];

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $GLOBALS['_POST'] = [];

        $this->subject = new \Tx_Phpunit_BackEnd_Ajax(false);

        $this->userSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectUserSettingsService($this->userSettingsService);
    }

    protected function tearDown()
    {
        $GLOBALS['_POST'] = $this->postBackup;
    }

    /**
     * @test
     */
    public function ajaxBrokerForFailureCheckboxParameterAndStateTrueSavesTrueStateToUserSettings()
    {
        $GLOBALS['_POST']['checkbox'] = 'failure';
        $GLOBALS['_POST']['state'] = '1';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $this->subject->ajaxBroker([], $ajax);

        self::assertTrue(
            $this->userSettingsService->getAsBoolean('failure')
        );
    }

    /**
     * @test
     */
    public function ajaxBrokerForFailureCheckboxParameterAndMissingStateSavesFalseStateToUserSettings()
    {
        $GLOBALS['_POST']['checkbox'] = 'failure';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $this->subject->ajaxBroker([], $ajax);

        self::assertFalse(
            $this->userSettingsService->getAsBoolean('failure')
        );
    }

    /**
     * @test
     */
    public function ajaxBrokerForFailureCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'failure';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForSuccessCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'success';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForErrorCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'error';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForSkippedCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'skipped';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForIncompleteCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'incomplete';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForTestDoxCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'testdox';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForCodeCoverageCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'codeCoverage';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForShowTimeCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'showTime';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForRunSeleniumTestsCheckboxParameterAddsSuccessContent()
    {
        $GLOBALS['_POST']['checkbox'] = 'runSeleniumTests';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('addContent')->with('success', true);
        $ajax->expects(self::never())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForMissingCheckboxParameterSetsError()
    {
        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }

    /**
     * @test
     */
    public function ajaxBrokerForInvalidCheckboxParameterSetsError()
    {
        $GLOBALS['_POST']['checkbox'] = 'anything else';

        /** @var AjaxRequestHandler|\PHPUnit_Framework_MockObject_MockObject $ajax */
        $ajax = $this->getMock(AjaxRequestHandler::class, [], ['']);
        $ajax->expects(self::once())->method('setError');

        $this->subject->ajaxBroker([], $ajax);
    }
}
