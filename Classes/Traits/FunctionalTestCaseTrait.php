<?php
namespace OliverKlee\Phpunit\Traits;

/*
 * This file is part of the PHPUnit TYPO3 extension.
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

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * This trait adds capabilities for (relatively) light-weight functional tests that work on the same TYPO3 instance
 * (unlike the functional tests of the TYPO3 Core).
 *
 * This trait is intended for test cases that extend \TYPO3\CMS\Core\Tests\UnitTestCase (directly on indirectly).
 * It provides the fields $objectManager, $persistenceManager and $testingFramework.
 *
 * When using this trait, make sure to always call $this->setUpFunctionalTestCase() in your setUp method and
 * $this->tearDownFunctionalTestCase() in your tearDown method. You do not need to call parent::setUp() and
 * porent::tearDown() anymore, though.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait FunctionalTestCaseTrait
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager = null;

    /**
     * @var \Tx_Phpunit_Framework
     */
    protected $testingFramework = null;

    /**
     * @var bool
     */
    protected $testWasSkipped = false;

    /**
     * Sets up everything that is necessary for running a functional test.
     *
     * This method _must_ be called first thing in your setUp method.
     *
     * @param string $tablePrefix
     *        the table name prefix of the extension for which this instance of
     *        the testing framework should be used
     * @param string[] $additionalTablePrefixes
     *        the additional table name prefixes of the extensions for which
     *        this instance of the testing framework should be used, may be empty
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function setUpFunctionalTestCase($tablePrefix, array $additionalTablePrefixes = [])
    {
        if (!$this instanceof UnitTestCase) {
            throw new \BadMethodCallException(
                'This test case needs to inherit from \\TYPO3\\CMS\\Core\\Tests\\UnitTestCase, but does not.',
                1481816400358
            );
        }

        if (!empty($GLOBALS['BE_USER'])) {
            $this->testWasSkipped = true;
            \PHPUnit_Framework_Assert::markTestSkipped(
                'This test must not be run from within a running TYPO3 BE or CLI.'
            );
        }

        Bootstrap::getInstance()
            ->loadConfigurationAndInitialize(false)
            ->loadTypo3LoadedExtAndExtLocalconf(false)
            ->disableCoreCache()
            ->initializeTypo3DbGlobal()
            ->loadExtensionTables(false);

        parent::setUp();

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
        $this->testingFramework = new \Tx_Phpunit_Framework($tablePrefix, $additionalTablePrefixes);
    }

    /**
     * Tears down the things from the functional test setUp.
     *
     * This method _must_ be called first thing in your tearDown method (directly before the parent::tearDown() call).
     *
     * @return void
     */
    public function tearDownFunctionalTestCase()
    {
        if (!$this->testWasSkipped) {
            $this->testingFramework->cleanUp();
            $this->persistenceManager->clearState();
        }

        parent::tearDown();
    }
}
