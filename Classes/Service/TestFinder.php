<?php
/*
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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions for finding test cases.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinder implements SingletonInterface
{
    /**
     * allowed test directory names
     *
     * @var string[]
     */
    static protected $allowedTestDirectoryNames = array('Tests/', 'tests/');

    /**
     * keys of the dummy extensions of the phpunit extension
     *
     * @var string[]
     */
    static protected $dummyExtensionKeys = array('aaa', 'bbb', 'ccc', 'ddd');

    /**
     * the cached result of findTestableForEverything
     *
     * @var Tx_Phpunit_Testable[]
     */
    protected $allTestables = array();

    /**
     * indicates whether $allTestables already has been filled
     *
     * @var bool
     */
    protected $allTestablesAreCached = false;

    /**
     * @var Tx_Phpunit_Interface_ExtensionSettingsService
     */
    protected $extensionSettingsService = null;

    /**
     * Injects the extension settings service.
     *
     * @param Tx_Phpunit_Interface_ExtensionSettingsService $service the service to inject
     *
     * @return void
     */
    public function injectExtensionSettingsService(Tx_Phpunit_Interface_ExtensionSettingsService $service)
    {
        $this->extensionSettingsService = $service;
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->extensionSettingsService);
    }

    /**
     * Checks whether there is testable code for a key.
     *
     * @param string $key
     *        the key to check, might be an extension key, the core key or
     *        any other string (even an empty string)
     *
     * @return bool TRUE if there is testable code with the given key, FALSE otherwise
     */
    public function existsTestableForKey($key)
    {
        if ($key === '') {
            return false;
        }

        $allTestables = $this->getTestablesForEverything();

        return isset($allTestables[$key]);
    }

    /**
     * Checks whether there is at least one tests directory in at least one
     * extension or in the TYPO3 Core.
     *
     * @return bool
     *         TRUE if there ist at least one test directory, FALSE otherwise
     */
    public function existsTestableForAnything()
    {
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
    public function getTestableForKey($key)
    {
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
     * @return Tx_Phpunit_Testable[]
     *         testable code for everything using the extension keys or the core key
     *         as array keys, might be empty
     */
    public function getTestablesForEverything()
    {
        if (!$this->allTestablesAreCached) {
            $this->allTestables = $this->getTestablesForExtensions();
            $this->allTestablesAreCached = true;
        }

        return $this->allTestables;
    }

    /**
     * Returns the testable code for all installed extensions, sorted in
     * alphabetical order by extension name.
     *
     * Extensions without a test directory and extensions in the "exclude list"
     * will be skipped.
     *
     * @return Tx_Phpunit_Testable[]
     *         testable code for the installed extensions using the extension keys
     *         as array keys, might be empty
     */
    public function getTestablesForExtensions()
    {
        $result = array();

        $extensionKeysToExamine = array_diff(
            $this->getLoadedExtensionKeys(),
            $this->getExcludedExtensionKeys(),
            $this->getDummyExtensionKeys()
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
     * @return int
     *         1 if both items need to be swapped, 0 if they have the same key,
     *         and -1 if the order is okay.
     */
    public function sortTestablesByKey(Tx_Phpunit_Testable $testable1, Tx_Phpunit_Testable $testable2)
    {
        return strcmp($testable1->getKey(), $testable2->getKey());
    }

    /**
     * Returns the keys of the loaded extensions.
     *
     * @return string[] the keys of the loaded extensions, might be empty
     */
    protected function getLoadedExtensionKeys()
    {
        return ExtensionManagementUtility::getLoadedExtensionListArray();
    }

    /**
     * Returns the keys of the extensions excluded from unit testing via the
     * phpunit configuration.
     *
     * @return string[] the keys of the excluded extensions, might be empty
     */
    protected function getExcludedExtensionKeys()
    {
        return GeneralUtility::trimExplode(
            ',',
            $this->extensionSettingsService->getAsString('excludeextensions'),
            true
        );
    }

    /**
     * Returns the keys of the extensions excluded from unit testing because
     * they are the dummy extensions of phpunit.
     *
     * @return string[] the keys of the dummy extensions, will not be empty
     */
    public function getDummyExtensionKeys()
    {
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
    protected function createTestableForSingleExtension($extensionKey)
    {
        $testsPath = $this->findTestsPathForExtension($extensionKey);

        /** @var Tx_Phpunit_Testable $testable */
        $testable = GeneralUtility::makeInstance('Tx_Phpunit_Testable');
        $testable->setKey($extensionKey);
        $testable->setTitle($extensionKey);
        $testable->setCodePath(ExtensionManagementUtility::extPath($extensionKey));
        $testable->setTestsPath($testsPath);
        $possibleIconFileNames = array('ext_icon.gif', 'ext_icon.png');
        foreach ($possibleIconFileNames as $fileNameCandidate) {
            if (file_exists(ExtensionManagementUtility::extPath($extensionKey) . $fileNameCandidate)) {
                $testable->setIconPath(ExtensionManagementUtility::extRelPath($extensionKey) . $fileNameCandidate);
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
    protected function findTestsPathForExtension($extensionKey)
    {
        if ($extensionKey === '') {
            throw new InvalidArgumentException('$extensionKey must not be empty.', 1334439819);
        }

        $testsPath = '';
        try {
            $extensionPath = ExtensionManagementUtility::extPath($extensionKey);

            foreach (self::$allowedTestDirectoryNames as $testDirectoryName) {
                if (is_dir($extensionPath . $testDirectoryName)) {
                    $testsPath = $extensionPath . $testDirectoryName;
                    break;
                }
            }
        } catch (BadFunctionCallException $e) {
            // Silently ignore missing extensions (e.g. extension directory does not exist)
        }

        if ($testsPath === '') {
            throw new Tx_Phpunit_Exception_NoTestsDirectory(
                'The extension "' . $extensionKey . '" does not have a tests directory.',
                1334439826
            );
        }

        return $testsPath;
    }
}
