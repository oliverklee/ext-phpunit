<?php

/**
 * This interface should be used for classes that should get called by a hook
 * when \Tx_Phpunit_Framework::cleanUp() is called.
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
