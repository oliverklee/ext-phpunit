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

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ProgressBarViewHelperTest extends \Tx_Phpunit_TestCase
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