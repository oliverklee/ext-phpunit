<?php

/**
 * This view helper renders the progress bar.
 *
 * @deprecated will be removed for PHPUnit 6.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_ViewHelpers_ProgressBarViewHelper extends \Tx_Phpunit_ViewHelpers_AbstractViewHelper
{
    /**
     * Renders and outputs this view helper.
     *
     * @return void
     */
    public function render()
    {
        $this->outputService->output(
            '<div class="progress-bar-wrap"><span id="progress-bar" class="wasSuccessful"></span></div>'
        );
    }
}
