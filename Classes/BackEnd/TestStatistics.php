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
 * This class provides functions for measuring the time and memory usage of tests.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_TestStatistics
{
    /**
     * @var bool
     */
    protected $isRunning = false;

    /**
     * @var float
     */
    protected $startTime = 0.0;

    /**
     * @var float
     */
    protected $currentTime = 0.0;

    /**
     * Starts the recording of the tests statistics.
     *
     * Note: This function may only be called once.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function start()
    {
        if ($this->isRunning) {
            throw new \BadMethodCallException('start may only be called once.', 1335895180);
        }

        $this->startTime = microtime(true);
        $this->isRunning = true;
    }

    /**
     * Stops the recording of the tests statistics.
     *
     * Note: This function may only be called once.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function stop()
    {
        if (!$this->isRunning) {
            throw new \BadMethodCallException('stop may only be called once after start has been called.', 1335895297);
        }

        $this->currentTime = microtime(true);
        $this->isRunning = false;
    }

    /**
     * Calculates the time since start has been called.
     *
     * @return float the time in seconds passed since start has been called, will be >= 0.0
     */
    public function getTime()
    {
        if ($this->isRunning) {
            $this->currentTime = microtime(true);
        }

        return $this->currentTime - $this->startTime;
    }
}
