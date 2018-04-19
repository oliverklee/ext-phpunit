<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

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

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestFinderTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_TestFinder
     */
    protected $subject = null;

    /**
     * backup of $GLOBALS['TYPO3_CONF_VARS']
     *
     * @var array
     */
    private $typo3ConfigurationVariablesBackup = [];

    /**
     * @var \Tx_Phpunit_TestingDataContainer
     */
    protected $extensionSettingsService = null;

    protected function setUp()
    {
        $this->typo3ConfigurationVariablesBackup = $GLOBALS['TYPO3_CONF_VARS'];

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

        $this->subject = $this->createAccessibleProxy();

        $this->extensionSettingsService = new \Tx_Phpunit_TestingDataContainer();
        $this->subject->injectExtensionSettingsService($this->extensionSettingsService);
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $this->typo3ConfigurationVariablesBackup;
    }

    /*
     * Utility functions
     */

    /**
     * Creates a subclass Tx_Phpunit_Service_TestFinder with the protected
     * functions made public.
     *
     * @return \Tx_Phpunit_Service_TestFinder an accessible proxy
     */
    private function createAccessibleProxy()
    {
        $className = 'Tx_Phpunit_Service_TestFinderAccessibleProxy';
        if (!class_exists($className, false)) {
            eval(
                'class ' . $className . ' extends Tx_Phpunit_Service_TestFinder {' .
                '  public function getLoadedExtensionKeys() {' .
                '    return parent::getLoadedExtensionKeys();' .
                '  }' .
                '  public function getExcludedExtensionKeys() {' .
                '    return parent::getExcludedExtensionKeys();' .
                '  }' .
                '  public function getDummyExtensionKeys() {' .
                '    return parent::getDummyExtensionKeys();' .
                '  }' .
                '  public function findTestsPathForExtension($extensionKey) {' .
                '    return parent::findTestsPathForExtension($extensionKey);' .
                '  }' .
                '}'
            );
        }

        return new $className();
    }

    /**
     * @test
     */
    public function createAccessibleProxyCreatesTestFinderSubclass()
    {
        self::assertInstanceOf(\Tx_Phpunit_Service_TestFinder::class, $this->createAccessibleProxy());
    }

    /*
     * Unit tests
     */

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getTestableForKeyForEmptyKeyThrowsException()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        $subject->getTestableForKey('');
    }

    /**
     * @test
     */
    public function getTestableForKeyForExistingKeyReturnsTestableForKey()
    {
        $testable = new \Tx_Phpunit_Testable();

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')->will(self::returnValue(['foo' => $testable]));

        self::assertSame(
            $testable,
            $subject->getTestableForKey('foo')
        );
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function getTestableForKeyForInexistentKeyThrowsException()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        $subject->getTestableForKey('bar');
    }

    /**
     * @test
     */
    public function existsTestableForKeyForEmptyKeyReturnsFalse()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        self::assertFalse(
            $subject->existsTestableForKey('')
        );
    }

    /**
     * @test
     */
    public function existsTestableForKeyForExistingKeyReturnsTrue()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        self::assertTrue(
            $subject->existsTestableForKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsTestableForKeyForInexistentKeyReturnsFalse()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $subject->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        self::assertFalse(
            $subject->existsTestableForKey('bar')
        );
    }

    /*
     * Tests concerning getTestablesForEverything
     */

    /**
     * @test
     */
    public function getTestablesForEverythingNoExtensionTestsReturnsEmptyArray()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getTestablesForExtensions']
        );
        $testFinder->expects(self::once())->method('getTestablesForExtensions')->will(self::returnValue([]));

        self::assertSame(
            [],
            $testFinder->getTestablesForEverything()
        );
    }

    /**
     * @test
     */
    public function getTestablesForEverythingForExtensionTestsReturnsExtensionTests()
    {
        $extensionTests = new \Tx_Phpunit_Testable();

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getTestablesForExtensions']
        );
        $testFinder->expects(self::once())->method('getTestablesForExtensions')
            ->will(self::returnValue(['foo' => $extensionTests]));

        self::assertSame(
            ['foo' => $extensionTests],
            $testFinder->getTestablesForEverything()
        );
    }

    /**
     * @test
     */
    public function getTestablesForEverythingCalledTwoTimesReturnsSameData()
    {
        $extensionTests = new \Tx_Phpunit_Testable();

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getTestablesForExtensions']
        );
        $testFinder->expects(self::any())->method('getTestablesForExtensions')
            ->will(self::returnValue(['foo' => $extensionTests]));

        self::assertSame(
            $testFinder->getTestablesForEverything(),
            $testFinder->getTestablesForEverything()
        );
    }

    /*
     * Tests concerning existsTestableForAnything
     */

    /**
     * @test
     */
    public function existsTestableForAnythingForNoTestablesReturnsFalse()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue([]));

        self::assertFalse(
            $testFinder->existsTestableForAnything()
        );
    }

    /**
     * @test
     */
    public function existsTestableForAnythingForOneTestableReturnsTrue()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable()]));

        self::assertTrue(
            $testFinder->existsTestableForAnything()
        );
    }

    /**
     * @test
     */
    public function existsTestableForAnythingForTwoTestablessReturnsTrue()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getTestablesForEverything']);
        $testFinder->expects(self::any())->method('getTestablesForEverything')
            ->will(self::returnValue(['foo' => new \Tx_Phpunit_Testable(), 'bar' => new \Tx_Phpunit_Testable()]));

        self::assertTrue(
            $testFinder->existsTestableForAnything()
        );
    }

    /**
     * @test
     */
    public function getLoadedExtensionKeysReturnsKeysOfAlwasRequiredExtensions()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['requiredExt'] = '';

        self::assertContains(
            'core',
            $this->subject->getLoadedExtensionKeys()
        );
    }

    /**
     * @test
     */
    public function getLoadedExtensionKeysReturnsPhpUnitKey()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = 'phpunit';

        self::assertArrayHasKey(
            'phpunit',
            array_flip($this->subject->getLoadedExtensionKeys())
        );
    }

    /**
     * Call-back function for checking whether $element is "Foo".
     *
     * @param string $element element to check, may be empty
     *
     * @return bool TRUE if $element is == "foo", FALSE otherwise
     */
    public function keepOnlyFooElements($element)
    {
        return $element === 'foo';
    }

    /**
     * @test
     */
    public function getExcludedExtensionKeysReturnsKeysOfExcludedExtensions()
    {
        $this->extensionSettingsService->set('excludeextensions', 'foo,bar');

        self::assertSame(
            ['foo', 'bar'],
            $this->subject->getExcludedExtensionKeys()
        );
    }

    /**
     * @test
     */
    public function getExcludedExtensionKeysForNoExcludedExtensionsReturnsEmptyArray()
    {
        $this->extensionSettingsService->set('excludeextensions', '');

        self::assertSame(
            [],
            $this->subject->getExcludedExtensionKeys()
        );
    }

    /**
     * @test
     */
    public function getExcludedExtensionKeysForNoPhpUnitConfigurationReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getExcludedExtensionKeys()
        );
    }

    /**
     * @test
     */
    public function getDummyExtensionKeysReturnsKeysOfPhpUnitDummyExtensions()
    {
        self::assertSame(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            $this->subject->getDummyExtensionKeys()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsCreatesTestableForSingleExtensionForInstalledExtensionsWithoutExcludedExtensions()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([
            'foo',
            'bar',
            'foobar',
        ]));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([
            'foo',
            'baz',
        ]));

        $testFinder->expects(self::at(2))->method('createTestableForSingleExtension')
            ->with('bar')->will(self::returnValue(new \Tx_Phpunit_Testable()));
        $testFinder->expects(self::at(3))->method('createTestableForSingleExtension')
            ->with('foobar')->will(self::returnValue(new \Tx_Phpunit_Testable()));

        $testFinder->getTestablesForExtensions();
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsCreatesTestableForSingleExtensionForInstalledExtensionsWithoutDummyExtensions()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'getDummyExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([
            'foo',
            'bar',
            'foobar',
        ]));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('getDummyExtensionKeys')->will(self::returnValue([
            'foo',
            'baz',
        ]));

        $testFinder->expects(self::at(3))->method('createTestableForSingleExtension')
            ->with('bar')->will(self::returnValue(new \Tx_Phpunit_Testable()));
        $testFinder->expects(self::at(4))->method('createTestableForSingleExtension')
            ->with('foobar')->will(self::returnValue(new \Tx_Phpunit_Testable()));

        $testFinder->getTestablesForExtensions();
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsSortsExtensionsByNsmeInAscendingOrder()
    {
        $testableForFoo = new \Tx_Phpunit_Testable();
        $testableForFoo->setKey('foo');
        $testableForBar = new \Tx_Phpunit_Testable();
        $testableForBar->setKey('bar');

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([
            'foo',
            'bar',
        ]));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));

        $testFinder->expects(self::at(2))->method('createTestableForSingleExtension')
            ->with('foo')->will(self::returnValue($testableForFoo));
        $testFinder->expects(self::at(3))->method('createTestableForSingleExtension')
            ->with('bar')->will(self::returnValue($testableForBar));

        self::assertSame(
            ['bar' => $testableForBar, 'foo' => $testableForFoo],
            $testFinder->getTestablesForExtensions()
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function findTestsPathForExtensionForExtensionWithEmptyExtensionKeyThrowsException()
    {
        $this->subject->findTestsPathForExtension('');
    }

    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_NoTestsDirectory
     */
    public function findTestsPathForExtensionForExtensionWithoutTestsPathThrowsException()
    {
        if (!ExtensionManagementUtility::isLoaded('ccc')) {
            self::markTestSkipped(
                'This test can only be run if the extension "ccc" from TestExtensions/ is installed.'
            );
        }

        $this->subject->findTestsPathForExtension('ccc');
    }

    /**
     * @test
     *
     * Note: This tests uses a lowercase compare because some systems use a
     * case-insensitive file system.
     */
    public function findTestsPathForExtensionForExtensionWithUpperFirstTestsDirectoryReturnsThatDirectory()
    {
        self::assertSame(
            strtolower(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'),
            strtolower($this->subject->findTestsPathForExtension('phpunit'))
        );
    }

    /**
     * @test
     *
     * Note: This tests uses a lowercase compare because some systems use a
     * case-insensitive file system.
     */
    public function findTestsPathForExtensionForExtensionWithLowerCaseTestsDirectoryReturnsThatDirectory()
    {
        if (!ExtensionManagementUtility::isLoaded('bbb')) {
            self::markTestSkipped(
                'This test can only be run if the extension "bbb" from TestExtensions/ is installed.'
            );
        }

        self::assertSame(
            strtolower(ExtensionManagementUtility::extPath('bbb') . 'tests/'),
            strtolower($this->subject->findTestsPathForExtension('bbb'))
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsForNoInstalledExtensionsReturnsEmptyArray()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(\Tx_Phpunit_Service_TestFinder::class, ['getLoadedExtensionKeys']);
        $testFinder->injectExtensionSettingsService($this->extensionSettingsService);
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([]));

        self::assertSame(
            [],
            $testFinder->getTestablesForExtensions()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsForOneInstalledExtensionsWithTestsReturnsOneTestableInstance()
    {
        $testableInstance = new \Tx_Phpunit_Testable();

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['foo']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('createTestableForSingleExtension')
            ->with('foo')->will(self::returnValue($testableInstance));

        self::assertSame(
            ['foo' => $testableInstance],
            $testFinder->getTestablesForExtensions()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsForTwoInstalledExtensionsWithTestsReturnsTwoResults()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([
            'foo',
            'bar',
        ]));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::at(2))->method('createTestableForSingleExtension')
            ->with('foo')->will(self::returnValue(new \Tx_Phpunit_Testable()));
        $testFinder->expects(self::at(3))->method('createTestableForSingleExtension')
            ->with('bar')->will(self::returnValue(new \Tx_Phpunit_Testable()));

        self::assertSame(
            2,
            count($testFinder->getTestablesForExtensions())
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsForOneInstalledExtensionsWithoutTestsReturnsEmptyArray()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['foo']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('foo')->will(self::throwException(new \Tx_Phpunit_Exception_NoTestsDirectory()));

        self::assertSame(
            [],
            $testFinder->getTestablesForExtensions()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsForOneExtensionsWithoutTestsAndOneWithTestsReturnsFirstExtension()
    {
        $testableInstance = new \Tx_Phpunit_Testable();

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            [
                'getLoadedExtensionKeys',
                'getExcludedExtensionKeys',
                'findTestsPathForExtension',
                'createTestableForSingleExtension',
            ]
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue([
            'foo',
            'bar',
        ]));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::at(2))->method('createTestableForSingleExtension')
            ->with('foo')->will(self::throwException(new \Tx_Phpunit_Exception_NoTestsDirectory()));
        $testFinder->expects(self::at(3))->method('createTestableForSingleExtension')
            ->with('bar')->will(self::returnValue($testableInstance));

        self::assertSame(
            ['bar' => $testableInstance],
            $testFinder->getTestablesForExtensions()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsProvidesTestableInstanceWithExtensionKey()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['phpunit']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('phpunit')->will(self::returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            'phpunit',
            $testable->getKey()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsProvidesTestableInstanceWithExtensionKeyAsTitleTitle()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['phpunit']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('phpunit')->will(self::returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            'phpunit',
            $testable->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsProvidesTestableInstanceWithCodePath()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['phpunit']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('phpunit')->will(self::returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            ExtensionManagementUtility::extPath('phpunit'),
            $testable->getCodePath()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsProvidesTestableInstanceWithTestsPath()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['phpunit']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('phpunit')->will(self::returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            ExtensionManagementUtility::extPath('phpunit') . 'Tests/',
            $testable->getTestsPath()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsWithGifIconProvidesTestableInstanceWithIconPath()
    {
        if (!ExtensionManagementUtility::isLoaded('user_phpunittest')) {
            self::markTestSkipped(
                'The Extension user_phpunittest is not installed, but needs to be installed. ' .
                'Please install it from EXT:phpunit/TestExtensions/user_phpunittest/.'
            );
        }

        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['user_phpunittest']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('user_phpunittest')->will(self::returnValue(ExtensionManagementUtility::extPath('user_phpunittest') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            ExtensionManagementUtility::extRelPath('user_phpunittest') . 'ext_icon.gif',
            $testable->getIconPath()
        );
    }

    /**
     * @test
     */
    public function getTestablesForExtensionsWithPngIconProvidesTestableInstanceWithIconPath()
    {
        /** @var \Tx_Phpunit_Service_TestFinder|\PHPUnit_Framework_MockObject_MockObject $testFinder */
        $testFinder = $this->getMock(
            \Tx_Phpunit_Service_TestFinder::class,
            ['getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension']
        );
        $testFinder->expects(self::once())->method('getLoadedExtensionKeys')->will(self::returnValue(['phpunit']));
        $testFinder->expects(self::once())->method('getExcludedExtensionKeys')->will(self::returnValue([]));
        $testFinder->expects(self::once())->method('findTestsPathForExtension')
            ->with('phpunit')->will(self::returnValue(ExtensionManagementUtility::extPath('phpunit') . 'Tests/'));

        /** @var \Tx_Phpunit_Testable $testable */
        $testable = array_pop($testFinder->getTestablesForExtensions());
        self::assertSame(
            ExtensionManagementUtility::extRelPath('phpunit') . 'ext_icon.png',
            $testable->getIconPath()
        );
    }
}
