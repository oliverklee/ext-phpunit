<?php
namespace OliverKlee\Phpunit\Tests\Unit;

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

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingDataContainerTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_TestingDataContainer();
    }

    /**
     * @test
     */
    public function classIsSingletonUserSettings()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_Interface_UserSettingsService',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getAsBooleanForMissingValueReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function setCanSetBooleanValueToFalse()
    {
        $key = 'foo';
        $this->subject->set($key, false);

        self::assertFalse(
            $this->subject->getAsBoolean($key)
        );
    }

    /**
     * @test
     */
    public function setCanSetBooleanValueToTrue()
    {
        $key = 'foo';
        $this->subject->set($key, true);

        self::assertTrue(
            $this->subject->getAsBoolean($key)
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnOneStringAsTrue()
    {
        $key = 'foo';
        $this->subject->set($key, '1');

        self::assertTrue(
            $this->subject->getAsBoolean($key)
        );
    }

    /**
     * @test
     */
    public function getAsIntegerForMissingValueReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function setCanSetIntegerValue()
    {
        $key = 'foo';
        $value = 42;
        $this->subject->set($key, $value);

        self::assertSame(
            $value,
            $this->subject->getAsInteger($key)
        );
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse()
    {
        $key = 'foo';
        $value = 0;
        $this->subject->set($key, $value);

        self::assertFalse(
            $this->subject->hasInteger($key)
        );
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue()
    {
        $key = 'foo';
        $value = 2;
        $this->subject->set($key, $value);

        self::assertTrue(
            $this->subject->hasInteger($key)
        );
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue()
    {
        $key = 'foo';
        $value = -1;
        $this->subject->set($key, $value);

        self::assertTrue(
            $this->subject->hasInteger($key)
        );
    }

    /**
     * @test
     */
    public function getAsStringForMissingValueReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     */
    public function setCanSetStringValue()
    {
        $key = 'foo';
        $value = 'Hello world!';
        $this->subject->set($key, $value);

        self::assertSame(
            $value,
            $this->subject->getAsString($key)
        );
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse()
    {
        $key = 'foo';
        $value = '';
        $this->subject->set($key, $value);

        self::assertFalse(
            $this->subject->hasString($key)
        );
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue()
    {
        $key = 'foo';
        $value = 'bar';
        $this->subject->set($key, $value);

        self::assertTrue(
            $this->subject->hasString($key)
        );
    }

    /**
     * @test
     */
    public function getAsArrayForMissingValueReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getAsArray('foo')
        );
    }

    /**
     * @test
     */
    public function setCanSetArrayValue()
    {
        $key = 'foo';
        $value = ['foo', 'foobar'];
        $this->subject->set($key, $value);

        self::assertSame(
            $value,
            $this->subject->getAsArray($key)
        );
    }
}
