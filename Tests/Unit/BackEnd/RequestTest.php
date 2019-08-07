<?php

namespace OliverKlee\PhpUnit\Tests\Unit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RequestTest extends TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = true;

    /**
     * Exclude TYPO3_DB from backup/restore of $GLOBALS
     * because resource types cannot be handled during serializing.
     *
     * TYPO3_CONF_VARS is also excluded because it contains a Closure
     * (which cannot be serialized).
     *
     * @var string[]
     */
    protected $backupGlobalsBlacklist = ['TYPO3_DB', 'TYPO3_CONF_VARS'];

    /**
     * @var \Tx_Phpunit_BackEnd_Request
     */
    protected $subject = null;

    /**
     * @var array
     */
    protected $testData = [
        'testValueString' => 'Hello world!',
        'testValueEmptyString' => '',
        'testValuePositiveInteger' => 42,
        'testValueZeroInteger' => 0,
        'testValueOneInteger' => 1,
        'testValueNegativeInteger' => -1,
        'testValueTrue' => true,
        'testValueFalse' => false,
    ];

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('The BE module is not available in TYPO3 CMS >= 8.');
        }

        $GLOBALS['_GET'] = [];
        $GLOBALS['_POST'] = [];

        $this->subject = new \Tx_Phpunit_BackEnd_Request();
    }

    /**
     * @test
     */
    public function classIsRequest()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_Interface_Request',
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
    public function getAsBooleanCanReturnFalseFromGet()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->getAsBoolean('testValueFalse')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnTrueFromGet()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->getAsBoolean('testValueTrue')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnOneStringFromGetAsTrue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->getAsBoolean('testValueOneInteger')
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
    public function getAsIntegerForExistingValueFromGetReturnsValue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertSame(
            42,
            $this->subject->getAsInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForZeroFromGetReturnsFalse()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->hasInteger('testValueZeroInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerFromGetReturnsTrue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerFromGetReturnsTrue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasInteger('testValueNegativeInteger')
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
    public function getAsStringForExistingValueFromGetReturnsValue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertSame(
            'Hello world!',
            $this->subject->getAsString('testValueString')
        );
    }

    /**
     * @test
     */
    public function getAsStringForExistingIntegerValueFromGetReturnsStringValue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertSame(
            '42',
            $this->subject->getAsString('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringFromGetReturnsFalse()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->hasString('testValueEmptyString')
        );
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringFromGetReturnsTrue()
    {
        $GLOBALS['_GET']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasString('testValueString')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnFalseFromPost()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->getAsBoolean('testValueFalse')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnTrueFromPost()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->getAsBoolean('testValueTrue')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnOneStringFromPostAsTrue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->getAsBoolean('testValueOneInteger')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerForExistingValueFromPostReturnsValue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertSame(
            42,
            $this->subject->getAsInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForZeroFromPostReturnsFalse()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->hasInteger('testValueZeroInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerFromPostReturnsTrue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerFromPostReturnsTrue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasInteger('testValueNegativeInteger')
        );
    }

    /**
     * @test
     */
    public function getAsStringForExistingValueFromPostReturnsValue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertSame(
            'Hello world!',
            $this->subject->getAsString('testValueString')
        );
    }

    /**
     * @test
     */
    public function getAsStringForExistingIntegerValueFromPostReturnsStringValue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertSame(
            '42',
            $this->subject->getAsString('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringFromPostReturnsFalse()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertFalse(
            $this->subject->hasString('testValueEmptyString')
        );
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringFromPostReturnsTrue()
    {
        $GLOBALS['_POST']['tx_phpunit'] = $this->testData;

        self::assertTrue(
            $this->subject->hasString('testValueString')
        );
    }

    /**
     * @test
     */
    public function postHasPrecedenceOverGet()
    {
        $GLOBALS['_GET']['tx_phpunit'] = ['foo' => 'getValue'];
        $GLOBALS['_POST']['tx_phpunit'] = ['foo' => 'postValue'];

        self::assertSame(
            'postValue',
            $this->subject->getAsString('foo')
        );
    }
}
