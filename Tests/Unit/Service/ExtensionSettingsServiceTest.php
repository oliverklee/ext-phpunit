<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ExtensionSettingsServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_ExtensionSettingsService
     */
    protected $subject = null;

    /**
     * @var string
     */
    private $extensionConfigurationBackup = null;

    /**
     * @var array
     */
    protected $testConfiguration = [
        'testValueString' => 'Hello world!',
        'testValueEmptyString' => '',
        'testValuePositiveInteger' => 42,
        'testValueZeroInteger' => 0,
        'testValueOneInteger' => 1,
        'testValueNegativeInteger' => -1,
        'testValueTrue' => true,
        'testValueFalse' => false,
        'testValueArray' => ['foo', 'bar'],
    ];

    protected function setUp()
    {
        $this->extensionConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'];
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['phpunit'] = serialize($this->testConfiguration);

        $this->subject = new \Tx_Phpunit_Service_ExtensionSettingsService();
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'] = $this->extensionConfigurationBackup;
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(
            SingletonInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function classIsSingletonExtensionSettings()
    {
        self::assertInstanceOf(
            'Tx_Phpunit_Interface_ExtensionSettingsService',
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
    public function getAsBooleanCanReturnFalseFromExtensionSettings()
    {
        self::assertFalse(
            $this->subject->getAsBoolean('testValueFalse')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnTrueFromExtensionSettings()
    {
        self::assertTrue(
            $this->subject->getAsBoolean('testValueTrue')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanCanReturnOneStringFromExtensionSettingsAsTrue()
    {
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
    public function getAsIntegerForExistingValueReturnsValueFromExtensionSettings()
    {
        self::assertSame(
            42,
            $this->subject->getAsInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasInteger('testValueZeroInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue()
    {
        self::assertTrue(
            $this->subject->hasInteger('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue()
    {
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
    public function getAsStringForExistingValueReturnsValueFromExtensionSettings()
    {
        self::assertSame(
            'Hello world!',
            $this->subject->getAsString('testValueString')
        );
    }

    /**
     * @test
     */
    public function getAsStringForExistingIntegerValueReturnsStringValueFromExtensionSettings()
    {
        self::assertSame(
            '42',
            $this->subject->getAsString('testValuePositiveInteger')
        );
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasString('testValueEmptyString')
        );
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue()
    {
        self::assertTrue(
            $this->subject->hasString('testValueString')
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
    public function getAsArrayForExistingValueReturnsValueFromExtensionSettings()
    {
        self::assertSame(
            ['foo', 'bar'],
            $this->subject->getAsArray('testValueArray')
        );
    }

    /**
     * @test
     */
    public function getAsArrayForExistingIntegerValueReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getAsArray('testValuePositiveInteger')
        );
    }
}
