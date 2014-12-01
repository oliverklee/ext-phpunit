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

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Exception_EmptyQueryResultTest extends Tx_Phpunit_TestCase {
	/**
	 * the saved content of $GLOBALS['TYPO3_DB']->debugOutput
	 *
	 * @var bool
	 */
	private $savedDebugOutput;

	/**
	 * the saved content of $GLOBALS['TYPO3_DB']->store_lastBuiltQuery
	 *
	 * @var bool
	 */
	private $savedStoreLastBuildQuery;

	protected function setUp() {
		$this->savedDebugOutput = $this->getDatabaseConnection()->debugOutput;
		$this->savedStoreLastBuildQuery = $this->getDatabaseConnection()->store_lastBuiltQuery;

		$this->getDatabaseConnection()->debugOutput = FALSE;
		$this->getDatabaseConnection()->store_lastBuiltQuery = TRUE;
	}

	protected function tearDown() {
		$this->getDatabaseConnection()->debugOutput = $this->savedDebugOutput;
		$this->getDatabaseConnection()->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_EmptyQueryResult
	 *
	 * @throws Tx_Phpunit_Exception_EmptyQueryResult
	 */
	public function exceptionCanBeThrown() {
		throw new Tx_Phpunit_Exception_EmptyQueryResult(1334438911);
	}

	/**
	 * @test
	 */
	public function messageAfterQueryWithLastQueryEnabledContainsLastQuery() {
		$this->getDatabaseConnection()->exec_SELECTquery('title', 'tx_phpunit_test', '');
		$subject = new Tx_Phpunit_Exception_EmptyQueryResult();

		$this->assertContains(
			'SELECT',
			$subject->getMessage()
		);
	}

	/**
	 * Returns $GLOBALS['TYPO3_DB'].
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
