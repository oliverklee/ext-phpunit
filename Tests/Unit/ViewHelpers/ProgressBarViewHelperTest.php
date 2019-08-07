<?php

namespace OliverKlee\PhpUnit\Tests\Unit\ViewHelpers;

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ProgressBarViewHelperTest extends TestCase
{
    /**
     * @var \Tx_Phpunit_ViewHelpers_ProgressBarViewHelper
     */
    protected $subject = null;

    /**
     * @var \Tx_Phpunit_Service_FakeOutputService
     */
    protected $outputService = null;

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $this->subject = new \Tx_Phpunit_ViewHelpers_ProgressBarViewHelper();

        $this->outputService = new \Tx_Phpunit_Service_FakeOutputService();
        $this->subject->injectOutputService($this->outputService);
    }

    /**
     * @test
     */
    public function classIsSubclassAbstractViewHelper()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_ViewHelpers_AbstractViewHelper',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function renderCreatesProgressBarHtmlId()
    {
        $this->subject->render();

        self::assertContains(
            'id="progress-bar"',
            $this->outputService->getCollectedOutput()
        );
    }
}
