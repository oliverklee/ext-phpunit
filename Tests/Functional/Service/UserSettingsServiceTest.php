<?php
namespace OliverKlee\Phpunit\Tests\Functional\Service;

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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Felix Rauch <rauch@skaiamail.de>
 */
class UserSettingsServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_UserSettingsService
     */
    protected $subject = null;

    /**
     * backup of $GLOBALS['BE_USER']
     *
     * @var BackendUserAuthentication
     */
    private $backEndUserBackup = null;

    /**
     * @var \Tx_Phpunit_Service_ExtensionSettingsService|null
     */
    protected $extensionSettingsService = null;

    /**
     * @var \Tx_Phpunit_Service_SeleniumService|null
     */
    protected $seleniumService = null;

    protected function setUp()
    {
        $this->backEndUserBackup = $GLOBALS['BE_USER'];
        $GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');

        $this->subject = new \Tx_Phpunit_Service_UserSettingsService();

        $this->extensionSettingsService = GeneralUtility::makeInstance(
            'Tx_Phpunit_Service_ExtensionSettingsService'
        );

        $this->seleniumService = GeneralUtility::makeInstance(
            'Tx_Phpunit_Service_SeleniumService',
            $this->extensionSettingsService
        );
    }

    protected function tearDown()
    {
        $GLOBALS['BE_USER'] = $this->backEndUserBackup;
    }

    /**
     * @test
     */
    public function isActiveForRunSeleniumTestsReturnsTrueIfSeleniumServerIsReachable()
    {
        if (!$this->seleniumService->isSeleniumServerRunning()) {
            self::markTestSkipped(
                'Selenium RC server not reachable (host=' .
                $this->seleniumService->getSeleniumHost() . ', port=' .
                $this->seleniumService->getSeleniumPort() . ').'
            );
        }

        $key = 'runSeleniumTests';

        self::assertTrue(
            $this->subject->isActive($key)
        );
    }

    /**
     * @test
     */
    public function isActiveForRunSeleniumTestsReturnsFalseIfSeleniumServerIsNotReachable()
    {
        if ($this->seleniumService->isSeleniumServerRunning()) {
            self::markTestSkipped(
                'Skipping test because Selenium RC server (host=' .
                $this->seleniumService->getSeleniumHost() . ', port=' .
                $this->seleniumService->getSeleniumPort() . ') ' .
                'is reachable.'
            );
        }

        $key = 'runSeleniumTests';

        self::assertFalse(
            $this->subject->isActive($key)
        );
    }

    /**
     * @test
     */
    public function isActiveForCodeCoverageReturnsTrueIfXdebugIsLoaded()
    {
        if (!extension_loaded('xdebug')) {
            self::markTestSkipped(
                'Skipping test because PHP extension xdebug is not loaded'
            );
        }

        $key = 'codeCoverage';

        self::assertTrue(
            $this->subject->isActive($key)
        );
    }

    /**
     * @test
     */
    public function isActiveForCodeCoverageReturnsFalseIfXdebugIsNotLoaded()
    {
        if (extension_loaded('xdebug')) {
            self::markTestSkipped(
                'Skipping test because PHP extension xdebug is loaded'
            );
        }

        $key = 'codeCoverage';

        self::assertFalse(
            $this->subject->isActive($key)
        );
    }
}
