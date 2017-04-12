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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class provides functions for outputting content.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_OutputService implements SingletonInterface
{
    /**
     * Echoes $output.
     *
     * @param string $output a string to echo, may be empty
     *
     * @return void
     */
    public function output($output)
    {
        echo $output;
    }

    /**
     * Flushes the output buffer.
     *
     * @return void
     */
    public function flushOutputBuffer()
    {
        flush();
    }
}
