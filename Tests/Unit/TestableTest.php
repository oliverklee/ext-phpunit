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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_TestableTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Testable
	 */
	private $subject;

	public function setUp() {
		$this->subject = new Tx_Phpunit_Testable();
	}

	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getKeyInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getKey()
		);
	}

	/**
	 * @test
	 */
	public function setKeySetsKey() {
		$this->subject->setKey('foo');

		$this->assertSame(
			'foo',
			$this->subject->getKey()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setKeyWithEmptyStringThrowsException() {
		$this->subject->setKey('');
	}

	/**
	 * @test
	 */
	public function getTitleInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleSetsTitle() {
		$this->subject->setTitle('White Russian');

		$this->assertSame(
			'White Russian',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleCanSetTitleToEmptyString() {
		$this->subject->setTitle('');

		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getCodePathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function setCodePathSetsCodePath() {
		$path = ExtensionManagementUtility::extPath('phpunit');
		$this->subject->setCodePath($path);

		$this->assertSame(
			$path,
			$this->subject->getCodePath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setCodePathWithEmptyStringThrowsException() {
		$this->subject->setCodePath('');
	}

	/**
	 * @test
	 */
	public function getTestsPathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function setTestsPathSetsTestsPath() {
		$path = ExtensionManagementUtility::extPath('phpunit') . 'Tests/';
		$this->subject->setTestsPath($path);

		$this->assertSame(
			$path,
			$this->subject->getTestsPath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setTestsPathWithEmptyStringThrowsException() {
		$this->subject->setTestsPath('');
	}

	/**
	 * @test
	 */
	public function getBlacklistInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function setBlacklistSetsBlacklist() {
		$fileNames = array('one file', 'another file');
		$this->subject->setBlacklist($fileNames);

		$this->assertSame(
			$fileNames,
			$this->subject->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function setBlacklistCanSetEmptyBlacklist() {
		$this->subject->setBlacklist(array());

		$this->assertSame(
			array(),
			$this->subject->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function getWhitelistInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function setWhitelistSetsWhitelist() {
		$fileNames = array('one file', 'another file');
		$this->subject->setWhitelist($fileNames);

		$this->assertSame(
			$fileNames,
			$this->subject->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function setWhitelistCanSetEmptyWhitelist() {
		$this->subject->setWhitelist(array());

		$this->assertSame(
			array(),
			$this->subject->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function getIconPathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function setIconPathSetsIconPath() {
		$this->subject->setIconPath('someIcon.gif');

		$this->assertSame(
			'someIcon.gif',
			$this->subject->getIconPath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setIconPathWithEmptyStringThrowsException() {
		$this->subject->setIconPath('');
	}
}