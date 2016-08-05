<?php
namespace OliverKlee\Phpunit\Tests\Functional\Traits;

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

use OliverKlee\Phpunit\Traits\FunctionalTestCaseInterface;
use OliverKlee\Phpunit\Traits\FunctionalTestCaseTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FunctionalTestCaseTraitTest extends UnitTestCase implements FunctionalTestCaseInterface
{
    use FunctionalTestCaseTrait;

    /**
     * @var bool
     */
    protected $backupGlobals = false;

    protected function setUp()
    {
        $this->setUpFunctionalTestCase('tx_phpunit');
    }

    protected function tearDown()
    {
        $this->tearDownFunctionalTestCase();
    }

    /**
     * @test
     */
    public function objectManagerIsAvailable()
    {
        self::assertInstanceOf(ObjectManagerInterface::class, $this->objectManager);
    }

    /**
     * @test
     */
    public function persistenceManagerIsAvailable()
    {
        self::assertInstanceOf(PersistenceManagerInterface::class, $this->persistenceManager);
    }

    /**
     * @test
     */
    public function frameworkIsAvailable()
    {
        self::assertInstanceOf(\Tx_Phpunit_Framework::class, $this->testingFramework);
    }
}
