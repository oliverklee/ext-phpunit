<?php
/***************************************************************
* Copyright notice
*
* (c) 2011-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_VfsStreamTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 */
	public function vfsStreamCanBeInstantiated() {
		new vfsStream();
	}

	/**
	 * @test
	 */
	public function vfsStreamContainerIteratorCanBeInstantiated() {
		new vfsStreamContainerIterator(array());
	}

	/**
	 * @test
	 */
	public function vfsStreamDirectoryCanBeInstantiated() {
		new vfsStreamDirectory('');
	}

	/**
	 * @test
	 *
	 * @expectedException vfsStreamException
	 * @throws vfsStreamException
	 */
	public function vfsStreamExceptionCanBeThrown() {
		throw new vfsStreamException('some message', 1234);
	}

	/**
	 * @test
	 */
	public function vfsStreamFileCanBeInstantiated() {
		new vfsStreamFile('');
	}

	/**
	 * @test
	 */
	public function vfsStreamWrapperCanBeInstantiated() {
		new vfsStreamWrapper();
	}
}
?>