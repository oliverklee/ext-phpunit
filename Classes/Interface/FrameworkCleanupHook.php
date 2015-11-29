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
 * This interface should be used for classes that should get called by a hook
 * when Tx_Phpunit_Framework::cleanUp() is called.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Phpunit_Interface_FrameworkCleanupHook
{
    /**
     * Cleans up phpunit after running a test.
     *
     * @return void
     */
    public function cleanUp();
}
