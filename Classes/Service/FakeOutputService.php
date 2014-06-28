<?php
/**
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
 * This class serves as a stand-in for the real output service, e.g., for unit testing.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_FakeOutputService extends Tx_Phpunit_Service_OutputService {
	/**
	 * @var string
	 */
	protected $collectedOutput = '';

	/**
	 * @var integer
	 */
	protected $numberOfFlushCalls = 0;

	/**
	 * Collects $output, but does not actually echo it.
	 *
	 * @param string $output a string to store, may be empty
	 *
	 * @return void
	 */
	public function output($output) {
		$this->collectedOutput .= $output;
	}

	/**
	 * Returns the collected output from all calls to output.
	 *
	 * @return string the collected output, might be empty
	 */
	public function getCollectedOutput() {
		return $this->collectedOutput;
	}

	/**
	 * Does not really flush the output buffer, but just counts the number of calls to this function.
	 *
	 * @return void
	 */
	public function flushOutputBuffer() {
		$this->numberOfFlushCalls++;
	}

	/**
	 * Returns how often flushOutputBuffer already has been called for this instance.
	 *
	 * @return integer the number of calls, will be >= 0
	 */
	public function getNumberOfFlushCalls() {
		return $this->numberOfFlushCalls;
	}
}