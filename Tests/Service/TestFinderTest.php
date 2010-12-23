<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Oliver Klee (typo3-coding@oliverklee.de)
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

/**
 * Testcase for the Tx_Phpunit_Service_TestFinder class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinderTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Phpunit_Service_TestFinder
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new Tx_Phpunit_Service_TestFinder();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertTrue(
			$this->fixture instanceof t3lib_Singleton
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertSame(
			'typo3_src/tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			'typo3_src/tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertSame(
			PATH_site . 'typo3_src/tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			PATH_site . 'typo3_src/tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsInCoreSourceInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsDirectlyInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForNoCoreTestsReturnsFalse() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertFalse(
			$this->fixture->hasCoreTests()
		);
	}
}
?>