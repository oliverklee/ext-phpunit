<?php

use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class provides functions for outputting content.
 *
 * @deprecated will be removed for PHPUnit 6.
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
