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
class Tx_Phpunit_Service_FakeOutputServiceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_FakeOutputService
	 */
	protected $subject = NULL;

	public function setUp() {
		$this->subject = new Tx_Phpunit_Service_FakeOutputService();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function classIsSubclassOfRealOutputService() {
		$this->assertInstanceOf(
			'Tx_Phpunit_Service_OutputService',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function outputWithNonEmptyStringNotEchosAnything() {
		ob_start();
		$this->subject->output('Hello world!');

		$this->assertSame(
			'',
			ob_get_contents()
		);

		ob_end_clean();
	}

	/**
	 * @test
	 */
	public function getCollectedOutputAfterOneOutputCallReturnsOutput() {
		$output = 'Hello world!';
		$this->subject->output($output);

		$this->assertSame(
			$output,
			$this->subject->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function getCollectedOutputAfterTwoOutputCallReturnsOutputsInCallingOrder() {
		$output1 = 'Hello world ...';
		$this->subject->output($output1);
		$output2 = ' and hello again.';
		$this->subject->output($output2);

		$this->assertSame(
			$output1 . $output2,
			$this->subject->getCollectedOutput()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfFlushCallsInitiallyReturnsZero() {
		$this->assertSame(
			0,
			$this->subject->getNumberOfFlushCalls()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfFlushCallsAfterOneCallToFlushOutputBufferReturnsOne() {
		$this->subject->flushOutputBuffer();

		$this->assertSame(
			1,
			$this->subject->getNumberOfFlushCalls()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfFlushCallsAfterTwoCallsToFlushOutputBufferReturnsTwo() {
		$this->subject->flushOutputBuffer();
		$this->subject->flushOutputBuffer();

		$this->assertSame(
			2,
			$this->subject->getNumberOfFlushCalls()
		);
	}
}