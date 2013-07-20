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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_TestStatisticsTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_TestStatistics
	 */
	protected $fixture = NULL;

	public function setUp() {
		$this->fixture = new Tx_Phpunit_BackEnd_TestStatistics();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 */
	public function startCalledTwoTimesThrowsException() {
		$this->fixture->start();
		$this->fixture->start();
	}

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 */
	public function stopWithoutStartThrowsException() {
		$this->fixture->stop();
	}

	/**
	 * @test
	 * @expectedException BadMethodCallException
	 */
	public function stopCalledTwoTimesAfterStartThrowsException() {
		$this->fixture->start();
		$this->fixture->stop();
		$this->fixture->stop();
	}

	/**
	 * @test
	 */
	public function getTimeInitiallyReturnsZero() {
		$this->assertSame(
			0.0,
			$this->fixture->getTime()
		);
	}

	/**
	 * @test
	 */
	public function getTimeWithoutStartAfterPauseReturnsZero() {
		usleep(100000);

		$this->assertSame(
			0.0,
			$this->fixture->getTime()
		);
	}

	/**
	 * @test
	 */
	public function getTimeAfterStartAfterPauseReturnsPassedTime() {
		$this->fixture->start();
		usleep(100000);

		$this->assertEquals(
			0.1,
			$this->fixture->getTime(),
			'', 0.02
		);
	}

	/**
	 * @test
	 */
	public function getTimeAfterStartAndStopReturnsPauseBeforeStop() {
		$this->fixture->start();
		usleep(100000);
		$this->fixture->stop();

		$this->assertEquals(
			0.1,
			$this->fixture->getTime(),
			'', 0.02
		);
	}

	/**
	 * @test
	 */
	public function getMemoryInitiallyReturnsZero() {
		$this->assertSame(
			0,
			$this->fixture->getMemory()
		);
	}

	/**
	 * @test
	 */
	public function getMemoryWithoutStartAfterMemoryUsageReturnsZero() {
		array();

		$this->assertSame(
			0,
			$this->fixture->getMemory()
		);
	}

	/**
	 * @test
	 */
	public function getMemoryAfterStartAfterMemoryUsageReturnsGreaterThanZero() {
		$this->fixture->start();
		array();

		$this->assertGreaterThan(
			0,
			$this->fixture->getMemory()
		);
	}
}
?>