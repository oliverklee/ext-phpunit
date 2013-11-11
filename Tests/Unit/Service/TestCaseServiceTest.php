<?php
/***************************************************************
* Copyright notice
*
* (c) 2013 Oliver Klee (typo3-coding@oliverklee.de)
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
class Tx_Phpunit_Service_TestCaseServiceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_TestCaseService
	 */
	private $subject = NULL;

	/**
	 * the absolute path to the fixtures directory for this test case
	 *
	 * @var string
	 */
	private $fixturesPath = '';

	/**
	 * @var Tx_Phpunit_TestingDataContainer
	 */
	protected $userSettingsService = NULL;

	public function setUp() {
		$this->subject = new Tx_Phpunit_Service_TestCaseService();

		$this->userSettingsService = new Tx_Phpunit_TestingDataContainer();
		$this->subject->injectUserSettingsService($this->userSettingsService);

		$this->fixturesPath = t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Service/Fixtures/';
	}

	public function tearDown() {
		$this->subject->__destruct();
		unset($this->subject, $this->userSettingsService);
	}

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertTrue(
			$this->subject instanceof t3lib_Singleton
		);
	}


	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestCaseFilesInDirectoryForEmptyPathThrowsException() {
		$this->subject->findTestCaseFilesInDirectory('');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestCaseFilesInDirectoryForInexistentPathThrowsException() {
		$this->subject->findTestCaseFilesInDirectory(
			$this->fixturesPath . 'DoesNotExist/'
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryForEmptyDirectoryReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->subject->findTestCaseFilesInDirectory($this->fixturesPath . 'Empty/')
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryFindsFileWithProperTestcaseFileName() {
		$path = 'OneTest.php';

		/** @var $subject Tx_Phpunit_Service_TestCaseService|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('isNotFixturesPath', 'isTestCaseFileName'));
		$subject->expects($this->any())->method('isNotFixturesPath')->will(($this->returnValue(TRUE)));
		$subject->expects($this->at(1))->method('isTestCaseFileName')
			->with($this->fixturesPath . $path)->will($this->returnValue(TRUE));

		$this->assertContains(
			$path,
			$subject->findTestCaseFilesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryNotFindsFileWithNonProperTestcaseFileName() {
		$path = 'OneTest.php';

		/** @var $subject Tx_Phpunit_Service_TestCaseService|PHPUnit_Framework_MockObject_MockObject */
		$subject = $this->getMock('Tx_Phpunit_Service_TestCaseService', array('isNotFixturesPath','isTestCaseFileName'));
		$subject->expects($this->any())->method('isNotFixturesPath')->will(($this->returnValue(TRUE)));
		$subject->expects($this->at(1))->method('isTestCaseFileName')
			->with($this->fixturesPath . $path)->will($this->returnValue(FALSE));

		$this->assertNotContains(
			$path,
			$subject->findTestCaseFilesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryFindsTestcaseInSubfolder() {
		$path = 'Service/TestFinderTest.php';

		$this->assertContains(
			$path,
			$this->subject->findTestCaseFilesInDirectory(t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/')
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryAcceptsPathWithTrailingSlash() {
		$result = $this->subject->findTestCaseFilesInDirectory(t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Service');

		$this->assertFalse(
			empty($result)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryAcceptsPathWithoutTrailingSlash() {
		$result = $this->subject->findTestCaseFilesInDirectory(
			t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Service'
		);

		$this->assertFalse(
			empty($result)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectorySortsFileNamesInAscendingOrder() {
		$result = $this->subject->findTestCaseFilesInDirectory(t3lib_extMgm::extPath('phpunit') . 'Tests/Unit/Service/');

		$fileName1 = 'DatabaseTest.php';
		$fileName2 = 'TestFinderTest.php';

		$this->assertTrue(
			array_search($fileName1, $result) < array_search($fileName2, $result)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryNotFindsFixtureClassesWithUppercasePath() {
		$path = 'OneTest.php';

		$this->assertNotContains(
			$path,
			$this->subject->findTestCaseFilesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCaseFilesInDirectoryNotFindsFixtureClassesWithLowercasePath() {
		if (!t3lib_extMgm::isLoaded('aaa')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "aaa" from Tests/Unit/Fixtures/Extensions/ is installed.'
			);
		}

		$path = t3lib_extMgm::extPath('aaa') . 'Tests/Unit/';
		$fileName = 'AnotherTest.php';

		$this->assertNotContains(
			$fileName,
			$this->subject->findTestCaseFilesInDirectory($path)
		);
	}


	/*
	 * Tests concerning isTestCaseFileName
	 */

	/**
	 * @test
	 */
	public function isTestCaseFileNameForTestSuffixReturnsTrue() {
		$this->assertTrue(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . 'OneTest.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForLowercaseTestSuffixReturnsTrue() {
		$this->assertTrue(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . 'onetest.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForLowercaseTestcaseSuffixReturnsTrue() {
		$this->assertTrue(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . 'Another_testcase.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForLowercaseNoUnderscoreTestcaseSuffixReturnsTrue() {
		$this->assertTrue(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . 'anothertestcase.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForOtherPhpFileReturnsFalse() {
		$this->assertFalse(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . 'SomethingDifferent.php'
			)
		);
	}

	/**
	 * @test
	 *
	 * @see http://forge.typo3.org/issues/9094
	 */
	public function isTestCaseFileNameForHiddenMacFileReturnsFalse() {
		$this->assertFalse(
			$this->subject->isTestCaseFileName(
				$this->fixturesPath . '._tx_tendbook_testTest.php'
			)
		);
	}


	/*
	 * Tests concerning isValidTestCaseClassName
	 */

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function isValidTestCaseClassNameForEmptyStringThrowsException() {
		$this->subject->isValidTestCaseClassName('');
	}

	/**
	 * Data provider for valid test case class names.
	 *
	 * @return array<array>
	 */
	public function validTestCaseClassNameDataProvider() {
		// Note: This currently does not contain any other classes as loading any other valid test case classes would
		// cause them to be listed as valid test cases in the user interface.
		$classNames = array(
			'subclass of Tx_Phpunit_TestCase' => array(get_class($this)),
		);

		return $classNames;
	}

	/**
	 * @test
	 *
	 * @dataProvider validTestCaseClassNameDataProvider
	 *
	 * @param string $className
	 */
	public function isValidTestCaseClassNameForValidClassNamesReturnsTrue($className) {
		$this->assertTrue(
			$this->subject->isValidTestCaseClassName($className)
		);
	}

	/**
	 * Data provider for invalid test case class names.
	 *
	 * @return array<array>
	 */
	public function invalidTestCaseClassNameDataProvider() {
		$this->createDummyInvalidTestCaseClasses();

		$invalidClassNames = array(
			'stdClass' => array('stdClass'),
			'inexistent class without valid suffix' => array('InexistentClassWithoutValidSuffix'),
			'inexistent class with valid Test suffix' => array('InexistentClassTest'),
			'inexistent class with valid _testcase suffix' => array('InexistentClass_testcase'),
			'existing class with valid Test suffix without valid base class' => array('SomeDummyInvalidTest'),
			'existing class with valid _testcase suffix without valid base class' => array('SomeDummyInvalid_testcase'),
			'PHPUnit extension base test class' => array('Tx_Phpunit_TestCase'),
			'PHPUnit framework base test class' => array('PHPUnit_Framework_TestCase'),
			'PHPUnit extension selenium base test class' => array('Tx_Phpunit_Selenium_TestCase'),
			'PHPUnit framework selenium base test class' => array('PHPUnit_Extensions_Selenium2TestCase'),
			'PHPUnit extension database base test class' => array('Tx_Phpunit_Database_TestCase'),
			'abstract subclass of PHPUnit extension base test class' => array('Tx_Phpunit_TestCase'),
		);

		$classNamesThatMightNotExist = array(
			'extbase selenium base test class (before 6.0)' => array('Tx_Extbase_SeleniumBaseTestCase'),
			'extbase selenium base test class (since 6.0)' => array('\\TYPO3\\CMS\\Extbase\\Tests\\SeleniumBaseTestCase'),
			'extbase base test class (before 1.3)' => array('Tx_Extbase_BaseTestCase'),
			'extbase base test class (1.3-4.7)' => array('Tx_Extbase_Tests_Unit_BaseTestCase'),
			'extbase unit base test class (since 6.0)' => array('TYPO3\\CMS\\Extbase\\Tests\\Unit\\BaseTestCase'),
			'extbase functional base test class (since 6.0)' => array('Tx_Extbase_Tests_Functional_BaseTestCase'),
			'Core base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\BaseTestCase'),
			'Core unit base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\UnitTestCase'),
			'Core functional base test class (since 6.0)' => array('TYPO3\\CMS\\Core\\Tests\\FunctionalTestCase'),
		);
		foreach ($classNamesThatMightNotExist as $key => $className) {
			if (class_exists($className[0], TRUE)) {
				$invalidClassNames[$key] = $className;
			}
		}

		return $invalidClassNames;
	}

	/**
	 * Creates some dummy invalid test case classes used for invalidTestCaseClassNameDataProvider.
	 *
	 * @return void
	 */
	protected function createDummyInvalidTestCaseClasses() {
		$classNamesWithoutBaseClasses = array('SomeDummyInvalidTest', 'SomeDummyInvalid_testcase');
		foreach ($classNamesWithoutBaseClasses as $className) {
			if (!class_exists($className, FALSE)) {
				eval('class ' . $className . ' {}');
			}
		}

		$abstractSubclassTestcaseName = 'AbstractDummyTestcase';
		if (!class_exists($abstractSubclassTestcaseName, FALSE)) {
			eval('class ' . $abstractSubclassTestcaseName . ' extends Tx_Phpunit_TestCase {}');
		}
	}

	/**
	 * @test
	 *
	 * @dataProvider invalidTestCaseClassNameDataProvider
	 *
	 * @param string $className
	 */
	public function isValidTestCaseClassNameForInvalidClassNamesReturnsFalse($className) {
		$this->assertFalse(
			$this->subject->isValidTestCaseClassName($className)
		);
	}
}
?>