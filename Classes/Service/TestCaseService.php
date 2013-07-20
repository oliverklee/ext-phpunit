<?php
/***************************************************************
* Copyright notice
*
* (c) 2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides functions for checking test cases.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestCaseService implements t3lib_Singleton {
	/**
	 * @var string
	 */
	const BASE_TEST_CASE_CLASS_NAME = 'PHPUnit_Framework_TestCase';

	/**
	 * @var string
	 */
	const SELENIUM_BASE_TEST_CASE_CLASS_NAME = 'PHPUnit_Extensions_Selenium2TestCase';

	/**
	 * suffixes that indicate that a file is a test case
	 *
	 * @var array<string>
	 */
	static protected $testCaseFileSuffixes = array(
		'Test.php',
		'test.php',
		'_testcase.php',
		'testcase.php',
	);

	/**
	 * class name suffixes that indicate that a class is a test case
	 *
	 * @var array<string>
	 */
	static protected $testCaseClassNameSuffixes = array(
		'Test',
		'_testcase',
	);

	/**
	 * @var Tx_Phpunit_Interface_UserSettingsService
	 */
	protected $userSettingsService = NULL;

	/**
	 * Injects the user settings service.
	 *
	 * @param Tx_Phpunit_Interface_UserSettingsService $service the service to inject
	 *
	 * @return void
	 */
	public function injectUserSettingsService(Tx_Phpunit_Interface_UserSettingsService $service) {
		$this->userSettingsService = $service;
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->userSettingsService);
	}

	/**
	 * Finds all files that are named like test files in the directory $directory
	 * and recursively all its subdirectories.
	 *
	 * @param string $directory
	 *        the absolute path of the directory in which to look for test cases
	 *
	 * @return array<string>
	 *         sorted file names of the test cases in the directory $directory relative
	 *         to $directory, will be empty if no test cases have been found
	 *
	 * @throws InvalidArgumentException
	 */
	public function findTestCaseFilesInDirectory($directory) {
		if ($directory === '') {
			throw new InvalidArgumentException('$directory must not be empty.', 1334439798);
		}
		if (!is_dir($directory)) {
			throw new InvalidArgumentException('The directory ' . $directory . ' does not exist.', 1334439804);
		}
		if (!is_readable($directory)) {
			throw new InvalidArgumentException(
				'The directory ' . $directory . ' exists, but is not readable.', 1334439813
			);
		}

		$directoryLength = strlen($directory);

		$testFiles = array();
		$allPhpFiles = t3lib_div::getAllFilesAndFoldersInPath(array(), $directory, 'php');
		foreach ($allPhpFiles as $filePath) {
			if ($this->isNotFixturesPath($filePath) && $this->isTestCaseFileName($filePath)) {
				$testFiles[] = substr($filePath, $directoryLength);
			}
		}

		sort($testFiles, SORT_STRING);

		return $testFiles;
	}

	/**
	 * Checks that a path does not contain "Fixtures" or "fixtures".
	 *
	 * @param string $path the absolute path of a file to check, may be empty
	 *
	 * @return boolean TRUE if $fileName is a valid test case path, FALSE otherwise
	 */
	protected function isNotFixturesPath($path) {
		return (stristr($path, '/fixtures/') === FALSE);
	}

	/**
	 * Checks whether a file name is named like a test case file name should be.
	 *
	 * @param string $path the absolute path of a file to check
	 *
	 * @return boolean TRUE if $fileName is names like a proper test case, FALSE otherwise
	 */
	public function isTestCaseFileName($path) {
		$fileName = basename($path);
		if ($this->isHiddenMacFile($fileName)) {
			return FALSE;
		}

		$isTestCase = FALSE;
		foreach (self::$testCaseFileSuffixes as $suffix) {
			if (substr($fileName, - strlen($suffix)) === $suffix) {
				$isTestCase = TRUE;
				break;
			}
		}

		return $isTestCase;
	}

	/**
	 * Checks whether $fileName is a hidden Mac file.
	 *
	 * @param string $fileName base name of a file to check
	 *
	 * @return boolean TRUE if $fileName is a hidden Mac file, FALSE otherwise
	 */
	protected function isHiddenMacFile($fileName) {
		return (substr($fileName, 0, 2) === '._');
	}

	/**
	 * Checks whether $className is the name of a valid test case class, i.e., whether it follows the naming guidelines,
	 * is a subclass of one of the test base classes, is not one of the base classes itself and is not abstract.
	 *
	 * @param string $className the class name to check, must not be empty
	 *
	 * @return boolean whether $className is the name of a valid test case class
	 *
	 * @throws InvalidArgumentException
	 */
	public function isValidTestCaseClassName($className) {
		if ($className === '') {
			throw new InvalidArgumentException('$className must not be empty.', 1354018635);
		}
		if (!$this->classNameHasTestCaseSuffix($className) || !class_exists($className, TRUE)
			|| ! $this->classNameIsNonAbstractSubclassOfValidBaseTestCase($className)
		) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Checks whether a class name has a name suffix that is allowed for test cases.
	 *
	 * @param string $className the class name to check, must not be empty
	 *
	 * @return boolean whether the class name has a suffix that is supported for test cases
	 */
	protected function classNameHasTestCaseSuffix($className) {
		$hasTestCaseSuffix = FALSE;

		foreach (self::$testCaseClassNameSuffixes as $suffixToCheck) {
			if (substr($className, - strlen($suffixToCheck)) === $suffixToCheck) {
				$hasTestCaseSuffix = TRUE;
				break;
			}
		}

		return $hasTestCaseSuffix;
	}

	/**
	 * Checks whether $className is the name of a non-abstract subclass of the test case base class.
	 *
	 * This function also checks for Selenium test cases whether Selenium tests are enabled in the user settings.
	 *
	 * @param string $className the class name to check, must not be empty
	 *
	 * @return boolean whether the corresponding class is both non-abstract and a subclass of the test case base class
	 */
	protected function classNameIsNonAbstractSubclassOfValidBaseTestCase($className) {
		$classReflection = new ReflectionClass($className);
		$result = !$classReflection->isAbstract() && $classReflection->isSubclassOf(self::BASE_TEST_CASE_CLASS_NAME);

		if (!$this->userSettingsService->getAsBoolean('runSeleniumTests')) {
			if (class_exists(self::SELENIUM_BASE_TEST_CASE_CLASS_NAME, TRUE)) {
				$result = $result && !$classReflection->isSubclassOf(self::SELENIUM_BASE_TEST_CASE_CLASS_NAME);
			}
		}

		return $result;
	}
}
?>