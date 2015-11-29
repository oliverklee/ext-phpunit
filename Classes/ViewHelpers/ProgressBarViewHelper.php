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

/**
 * This view helper renders the progress bar.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_ViewHelpers_ProgressBarViewHelper extends Tx_Phpunit_ViewHelpers_AbstractViewHelper
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
