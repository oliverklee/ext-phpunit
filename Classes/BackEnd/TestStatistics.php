<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Oliver Klee <typo3-coding@oliverklee.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides functions for measuring the time and memory usage of tests.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_TestStatistics {
	/**
	 * @var boolean
	 */
	protected $isRunning = FALSE;

	/**
	 * @var float
	 */
	protected $startTime = 0.0;

	/**
	 * @var float
	 */
	protected $currentTime = 0.0;

	/**
	 * @var integer
	 */
	protected $startMemory = 0;

	/**
	 * @var integer
	 */
	protected $currentMemory = 0;

	/**
	 * Starts the recording of the tests statistics.
	 *
	 * Note: This function may only be called once.
	 *
	 * @return void
	 *
	 * @throws BadMethodCallException
	 */
	public function start() {
		if ($this->isRunning) {
			throw new BadMethodCallException('start may only be called once.', 1335895180);
		}

		$this->startTime = microtime(TRUE);
		$this->startMemory = memory_get_usage();

		$this->isRunning = TRUE;
	}

	/**
	 * Stops the recording of the tests statistics.
	 *
	 * Note: This function may only be called once.
	 *
	 * @return void
	 *
	 * @throws BadMethodCallException
	 */
	public function stop() {
		if (!$this->isRunning) {
			throw new BadMethodCallException('stop may only be called once after start has been called.', 1335895297);
		}

		$this->currentTime = microtime(TRUE);
		$this->currentMemory = memory_get_usage();

		$this->isRunning = FALSE;
	}

	/**
	 * Calculates the time since start has been called.
	 *
	 * @return float the time in seconds passed since start has been called, will be >= 0.0
	 */
	public function getTime() {
		if ($this->isRunning) {
			$this->currentTime = microtime(TRUE);
		}

		return $this->currentTime - $this->startTime;
	}

	/**
	 * Calculates the memory usage since start has been called.
	 *
	 * @return integer the memory used (in Bytes) since start has been called, will be >= 0
	 */
	public function getMemory() {
		if ($this->isRunning) {
			$this->currentMemory = memory_get_usage();
		}

		return $this->currentMemory - $this->startMemory;

	}
}
?>