<?php

namespace OliverKlee\PhpUnit\Tests\Unit;

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestableTest extends TestCase
{
    /**
     * @var \Tx_Phpunit_Testable
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_Testable();
    }

    /**
     * @test
     */
    public function getKeyInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getKey()
        );
    }

    /**
     * @test
     */
    public function setKeySetsKey()
    {
        $this->subject->setKey('foo');

        self::assertSame(
            'foo',
            $this->subject->getKey()
        );
    }

    /**
     * @test
     */
    public function setKeyWithEmptyStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setKey('');
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('White Russian');

        self::assertSame(
            'White Russian',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleCanSetTitleToEmptyString()
    {
        $this->subject->setTitle('');

        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getCodePathInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getCodePath()
        );
    }

    /**
     * @test
     */
    public function setCodePathSetsCodePath()
    {
        $path = ExtensionManagementUtility::extPath('phpunit');
        $this->subject->setCodePath($path);

        self::assertSame(
            $path,
            $this->subject->getCodePath()
        );
    }

    /**
     * @test
     */
    public function setCodePathWithEmptyStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setCodePath('');
    }

    /**
     * @test
     */
    public function getTestsPathInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTestsPath()
        );
    }

    /**
     * @test
     */
    public function setTestsPathSetsTestsPath()
    {
        $path = ExtensionManagementUtility::extPath('phpunit') . 'Tests/';
        $this->subject->setTestsPath($path);

        self::assertSame(
            $path,
            $this->subject->getTestsPath()
        );
    }

    /**
     * @test
     */
    public function setTestsPathWithEmptyStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setTestsPath('');
    }

    /**
     * @test
     */
    public function getBlacklistInitiallyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getBlacklist()
        );
    }

    /**
     * @test
     */
    public function setBlacklistSetsBlacklist()
    {
        $fileNames = ['one file', 'another file'];
        $this->subject->setBlacklist($fileNames);

        self::assertSame(
            $fileNames,
            $this->subject->getBlacklist()
        );
    }

    /**
     * @test
     */
    public function setBlacklistCanSetEmptyBlacklist()
    {
        $this->subject->setBlacklist([]);

        self::assertSame(
            [],
            $this->subject->getBlacklist()
        );
    }

    /**
     * @test
     */
    public function getWhitelistInitiallyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getWhitelist()
        );
    }

    /**
     * @test
     */
    public function setWhitelistSetsWhitelist()
    {
        $fileNames = ['one file', 'another file'];
        $this->subject->setWhitelist($fileNames);

        self::assertSame(
            $fileNames,
            $this->subject->getWhitelist()
        );
    }

    /**
     * @test
     */
    public function setWhitelistCanSetEmptyWhitelist()
    {
        $this->subject->setWhitelist([]);

        self::assertSame(
            [],
            $this->subject->getWhitelist()
        );
    }

    /**
     * @test
     */
    public function getIconPathInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getIconPath()
        );
    }

    /**
     * @test
     */
    public function setIconPathSetsIconPath()
    {
        $this->subject->setIconPath('someIcon.gif');

        self::assertSame(
            'someIcon.gif',
            $this->subject->getIconPath()
        );
    }

    /**
     * @test
     */
    public function setIconPathWithEmptyStringThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setIconPath('');
    }
}
