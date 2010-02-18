<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007-2010 Kasper Ligaard (kasperligaard@gmail.com)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_t3lib . 'class.t3lib_tcemain.php');

/**
 * Test case for checking the PHPUnit 3.1.9
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */
class tx_phpunit_test_testcase extends tx_phpunit_testcase {
	/**
	 * @test
	 */
	public function newArrayIsEmpty() {
		// Create the Array fixture.
		$fixture = array();

		// Assert that the size of the Array fixture is 0.
		$this->assertEquals(0, sizeof($fixture));
	}

	/**
	 * @test
	 */
	public function thisCaseIsMarkedAsSkipped() {
		$this->markTestSkipped('This test is skipped while testing.');
	}

	/**
	 * @test
	 */
	public function thisCaseIsMarkedAsNotImplemented() {
		$this->markTestIncomplete('This test as incomplete while not implemented for testing.');
	}
}
?>