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