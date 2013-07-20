<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides functions for finding test cases.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinder implements t3lib_Singleton {
	/**
	 * allowed test directory names
	 *
	 * @var array<string>
	 */
	static protected $allowedTestDirectoryNames = array('Tests/', 'tests/');

	/**
	 * keys of the dummy extensions of the phpunit extension
	 *
	 * @var array<string>
	 */
	static protected $dummyExtensionKeys = array('aaa', 'bbb', 'ccc', 'ddd');

	/**
	 * the cached result of findTestableForEverything
	 *
	 * @var array
	 */
	protected $allTestables = array();

	/**
	 * indicates whether $allTestables already has been filled
	 *
	 * @var boolean
	 */
	protected $allTestablesAreCached = FALSE;

	/**
	 * @var Tx_Phpunit_Interface_ExtensionSettingsService
	 */
	protected $extensionSettingsService = NULL;

	/**
	 * Injects the extension settings service.
	 *
	 * @param Tx_Phpunit_Interface_ExtensionSettingsService $service the service to inject
	 *
	 * @return void
	 */
	public function injectExtensionSettingsService(Tx_Phpunit_Interface_ExtensionSettingsService $service) {
		$this->extensionSettingsService = $service;
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->extensionSettingsService);
	}

	/**
	 * Gets the path of the TYPO3 Core unit tests relative to PATH_site.
	 *
	 * If there is no tests directory for the Core, this function will return an empty string.
	 *
	 * @return string
	 *         the path of the TYPO3 Core unit tests relative to PATH_site,
	 *         will be empty if there is no Core tests directory
	 */
	public function getRelativeCoreTestsPath() {
		$possibleTestsPath1 = 'tests/';
		$possibleTestsPath2 = 'typo3_src/tests/';

		if (file_exists(PATH_site .  $possibleTestsPath1)) {
			$testsPath = $possibleTestsPath1;
		} elseif (file_exists(PATH_site . $possibleTestsPath2)) {
			$testsPath = $possibleTestsPath2;
		} else {
			$testsPath = '';
		}

		return $testsPath;
	}

	/**
	 * Gets the absolute path of the TYPO3 Core unit tests.
	 *
	 * If there is no tests directory for the Core, this function will return an empty string.
	 *
	 * @return string
	 *         the absolute path of the TYPO3 Core unit tests,
	 *         will be empty if there is no Core tests directory
	 */
	public function getAbsoluteCoreTestsPath() {
		if (!$this->hasCoreTests()) {
			return '';
		}

		return PATH_site . $this->getRelativeCoreTestsPath();
	}

	/**
	 * Checks whether the TYPO3 Core has a tests directory.
	 *
	 * @return boolean TRUE if the TYPO3 Core has a tests directory, FALSE otherwise
	 */
	public function hasCoreTests() {
		return ($this->getRelativeCoreTestsPath() !== '');
	}

	/**
	 * Checks whether there is testable code for a key.
	 *
	 * @param string $key
	 *        the key to check, might be an extension key, the core key or
	 *        any other string (even an empty string)
	 *
	 * @return boolean TRUE if there is testable code with the given key, FALSE otherwise
	 */
	public function existsTestableForKey($key) {
		if ($key === '') {
			return FALSE;
		}

		$allTestables = $this->getTestablesForEverything();

		return isset($allTestables[$key]);
	}

	/**
	 * Checks whether there is at least one tests directory in at least one
	 * extension or in the TYPO3 Core.
	 *
	 * @return boolean
	 *         TRUE if there ist at least one test directory, FALSE otherwise
	 */
	public function existsTestableForAnything() {
		$testablesForEverything = $this->getTestablesForEverything();

		return !empty($testablesForEverything);
	}

	/**
	 * Returns the testable code for the given key.
	 *
	 * @param string $key
	 *        the key for which to get the testable, must an extension key or the core key, must not be empty
	 *
	 * @return Tx_Phpunit_Testable the testable for the given key
	 *
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 */
	public function getTestableForKey($key) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.', 1334664441);
		}
		if (!$this->existsTestableForKey($key)) {
			throw new BadMethodCallException('There is no testable for this key: ' . $key, 1334664552);
		}

		$allTestables = $this->getTestablesForEverything();
		return $allTestables[$key];
	}

	/**
	 * Returns the testable code instance for everything, i.e., the core and
	 * all installed extensions.
	 *
	 * @return array<Tx_Phpunit_Testable>
	 *         testable code for everything using the extension keys or the core key
	 *         as array keys, might be empty
	 */
	public function getTestablesForEverything() {
		if (!$this->allTestablesAreCached) {
			$this->allTestables = array_merge(
				$this->getTestablesForExtensions(), $this->getTestableForCore()
			);

			$this->allTestablesAreCached = TRUE;
		}

		return $this->allTestables;
	}

	/**
	 * Returns the testable code for the TYPO3 Core.
	 *
	 * @return array<Tx_Phpunit_Testable>
	 *         testable code for the TYPO3 core, will have exactly one element if
	 *         there are Core tests (using the core key as array key),
	 *         will be empty if there are no Core tests
	 */
	public function getTestableForCore() {
		if (!$this->hasCoreTests()) {
			return array();
		}

		/** @var $coreTests Tx_Phpunit_Testable */
		$coreTests = t3lib_div::makeInstance('Tx_Phpunit_Testable');
		$coreTests->setType(Tx_Phpunit_Testable::TYPE_CORE);
		$coreTests->setKey(Tx_Phpunit_Testable::CORE_KEY);
		$coreTests->setTitle('TYPO3 Core');
		$coreTests->setCodePath(PATH_site);
		$coreTests->setTestsPath($this->getAbsoluteCoreTestsPath());
		$coreTests->setIconPath(t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png');

		return array(Tx_Phpunit_Testable::CORE_KEY => $coreTests);
	}

	/**
	 * Returns the testable code for all installed extensions, sorted in
	 * alphabetical order by extension name.
	 *
	 * Extensions without a test directory and extensions in the "exclude list"
	 * will be skipped.
	 *
	 * @return array<Tx_Phpunit_Testable>
	 *         testable code for the installed extensions using the extension keys
	 *         as array keys, might be empty
	 */
	public function getTestablesForExtensions() {
		$result = array();

		$extensionKeysToExamine = array_diff(
			$this->getLoadedExtensionKeys(),
			$this->getExcludedExtensionKeys(), $this->getDummyExtensionKeys()
		);

		foreach ($extensionKeysToExamine as $extensionKey) {
			try {
				$result[$extensionKey] = $this->createTestableForSingleExtension($extensionKey);
			} catch (Tx_Phpunit_Exception_NoTestsDirectory $exception) {
				// Just skip extensions without a tests directory.
			}
		}

		uasort($result, array($this, 'sortTestablesByKey'));

		return $result;
	}

	/**
	 * Callback function for comparing the keys of $testable1 and $testable2.
	 *
	 * @param Tx_Phpunit_Testable $testable1 the first item to compare
	 * @param Tx_Phpunit_Testable $testable2 the second item to compare
	 *
	 * @return integer
	 *         1 if both items need to be swapped, 0 if they have the same key,
	 *         and -1 if the order is okay.
	 */
	public function sortTestablesByKey(Tx_Phpunit_Testable $testable1, Tx_Phpunit_Testable $testable2) {
		return strcmp($testable1->getKey(), $testable2->getKey());
	}

	/**
	 * Returns the keys of the loaded extensions.
	 *
	 * @return array<string> the keys of the loaded extensions, might be empty
	 */
	protected function getLoadedExtensionKeys() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 6000000) {
			$allExtensionKeys = t3lib_extMgm::getLoadedExtensionListArray();
		} else {
			$requiredExtensionList = t3lib_extMgm::getRequiredExtensionList();
			$loadedExtensionList = isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'])
				? $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] : '';

			$allExtensionKeys = array_unique(
				t3lib_div::trimExplode(',', $loadedExtensionList . ',' . $requiredExtensionList, TRUE)
			);
		}

		return $allExtensionKeys;
	}

	/**
	 * Returns the keys of the extensions excluded from unit testing via the
	 * phpunit configuration.
	 *
	 * @return array<string> the keys of the excluded extensions, might be empty
	 */
	protected function getExcludedExtensionKeys() {
		return t3lib_div::trimExplode(',', $this->extensionSettingsService->getAsString('excludeextensions'), TRUE);
	}

	/**
	 * Returns the keys of the extensions excluded from unit testing because
	 * they are the dummy extensions of phpunit.
	 *
	 * @return array<string> the keys of the dummy extensions, will not be empty
	 */
	public function getDummyExtensionKeys() {
		return self::$dummyExtensionKeys;
	}

	/**
	 * Creates the testable code instance for the extension with the given key.
	 *
	 * @param string $extensionKey the key of an installed extension, must not be empty
	 *
	 * @return Tx_Phpunit_Testable the test-relevant data of the installed extension
	 *
	 * @throws Tx_Phpunit_Exception_NoTestsDirectory if the given extension has no tests directory
	 */
	protected function createTestableForSingleExtension($extensionKey) {
		$testsPath = $this->findTestsPathForExtension($extensionKey);

		/** @var $testable Tx_Phpunit_Testable */
		$testable = t3lib_div::makeInstance('Tx_Phpunit_Testable');
		$testable->setType(Tx_Phpunit_Testable::TYPE_EXTENSION);
		$testable->setKey($extensionKey);
		$testable->setTitle($extensionKey);
		$testable->setCodePath(t3lib_extMgm::extPath($extensionKey));
		$testable->setTestsPath($testsPath);
		$possibleIconFileNames = array('ext_icon.gif', 'ext_icon.png');
		foreach ($possibleIconFileNames as $fileNameCandidate) {
			if (file_exists(t3lib_extMgm::extPath($extensionKey) . $fileNameCandidate)) {
				$testable->setIconPath(t3lib_extMgm::extRelPath($extensionKey) . $fileNameCandidate);
				break;
			}
		}

		return $testable;
	}

	/**
	 * Finds the absolute path to the tests of the extension with the key $extensionKey.
	 *
	 * @param string $extensionKey the key of an installed extension, must not be empty
	 *
	 * @return string
	 *         the absolute path of the tests directory of the given extension
	 *         (might differ in case from the actual tests directory on case-insensitive
	 *         file systems)
	 *
	 * @throws InvalidArgumentException
	 * @throws Tx_Phpunit_Exception_NoTestsDirectory if the given extension has no tests directory
	 */
	protected function findTestsPathForExtension($extensionKey) {
		if ($extensionKey === '') {
			throw new InvalidArgumentException('$extensionKey must not be empty.', 1334439819);
		}

		$testsPath = '';
		$extensionPath = t3lib_extMgm::extPath($extensionKey);
		foreach (self::$allowedTestDirectoryNames as $testDirectoryName) {
			if (is_dir($extensionPath . $testDirectoryName)) {
				$testsPath = $extensionPath . $testDirectoryName;
				break;
			}
		}

		if ($testsPath === '') {
			throw new Tx_Phpunit_Exception_NoTestsDirectory(
				'The extension "' . $extensionKey . '" does not have a tests directory.', 1334439826
			);
		}

		return $testsPath;
	}
}
?>