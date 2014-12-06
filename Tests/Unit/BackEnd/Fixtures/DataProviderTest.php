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
 * This is a fixture testcase used for testing data providers.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Tests_BackEnd_Fixtures_DataProviderTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 */
	public function testOne() {
	}

	/**
	 * @test
	 */
	public function testTwo() {
	}

	/**
	 * Data provider that just returns three empty arrays.
	 *
	 * @see dataProviderTest
	 *
	 * @return array[]
	 */
	public function dataProvider() {
		return array(
			'some data' => array(),
			'more data' => array(),
			'and even more data' => array(),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider dataProvider
	 */
	public function dataProviderTest() {
	}

	/**
	 * @test
	 */
	public function testThree() {
	}

	/**
	 * @test
	 */
	public function testFour() {
	}
}