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

use OliverKlee\Phpunit\Tests\Unit\Fixtures\ProtectedClass;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var ProtectedClass
     */
    private $protectedClassInstance = null;

    /**
     * @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject|ProtectedClass
     */
    private $mock = null;

    /**
     * @var \Tx_Phpunit_BackEnd_Module|\PHPUnit_Framework_MockObject_MockObject|\Tx_Phpunit_Interface_AccessibleObject
     */
    private $accessibleMock = null;

    /**
     * Backup of static property of the accessible mock.
     *
     * @var string
     */
    private $staticProperty = '';

    protected function setUp()
    {
        require_once ExtensionManagementUtility::extPath('phpunit') . 'Tests/Unit/Fixtures/ProtectedClass.php';

        $this->protectedClassInstance = new ProtectedClass();
        $this->mock = $this->getMock(ProtectedClass::class, ['dummy']);
        $this->accessibleMock = $this->getAccessibleMock(
            ProtectedClass::class,
            ['dummy']
        );
        $this->staticProperty = ProtectedClass::getStaticProperty();
    }

    protected function tearDown()
    {
        ProtectedClass::setStaticProperty($this->staticProperty);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getAccessibleMockWithEmptyClassNameThrowsException()
    {
        $this->getAccessibleMock('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function callForEmptyMethodNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_call('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function callRefForEmptyMethodNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_callRef('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setForEmptyPropertyNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_set('', '');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setRefForEmptyPropertyNameInAccessibleMockObjectThrowsException()
    {
        $value = '';
        $this->accessibleMock->_setRef('', $value);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_setStatic('', '');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getForEmptyPropertyNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_get('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException()
    {
        $this->accessibleMock->_getStatic('');
    }

    /**
     * @test
     */
    public function protectedPropertyForFixtureIsNotDirectlyAccessible()
    {
        self::assertFalse(
            in_array('protectedProperty', get_object_vars($this->protectedClassInstance), true)
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForFixtureIsNotDirectlyAccessible()
    {
        self::assertFalse(
            array_key_exists('protectedStaticProperty', get_class_vars(get_class($this->protectedClassInstance)))
        );
    }

    /**
     * @test
     */
    public function publicPropertyForFixtureIsDirectlyAccessible()
    {
        self::assertSame(
            'This is a public property.',
            $this->protectedClassInstance->publicProperty
        );
    }

    /**
     * @test
     */
    public function protectedMethodForFixtureIsNotDirectlyCallable()
    {
        self::assertFalse(
            is_callable([$this->protectedClassInstance, 'protectedMethod'])
        );
    }

    /**
     * @test
     */
    public function publicMethodForFixtureIsDirectlyCallable()
    {
        self::assertTrue(
            is_callable([$this->protectedClassInstance, 'publicMethod'])
        );
    }

    /**
     * @test
     */
    public function protectedPropertyForMockObjectIsNotDirectlyAccessible()
    {
        self::assertFalse(
            in_array('protectedProperty', get_object_vars($this->mock), true)
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForMockObjectIsNotDirectlyAccessible()
    {
        self::assertFalse(
            array_key_exists('protectedStaticProperty', get_class_vars(get_class($this->mock)))
        );
    }

    /**
     * @test
     */
    public function publicPropertyForMockObjectIsDirectlyAccessible()
    {
        self::assertSame(
            'This is a public property.',
            $this->mock->publicProperty
        );
    }

    /**
     * @test
     */
    public function protectedMethodForMockObjectIsNotDirectlyCallable()
    {
        self::assertFalse(
            is_callable([$this->mock, 'protectedMethod'])
        );
    }

    /**
     * @test
     */
    public function publicMethodForMockObjectIsDirectlyCallable()
    {
        self::assertTrue(
            is_callable([$this->mock, 'publicMethod'])
        );
    }

    /**
     * @test
     */
    public function protectedPropertyForAccessibleMockObjectIsDirectlyAccessible()
    {
        self::assertSame(
            'This is a protected property.',
            $this->accessibleMock->_get('protectedProperty')
        );
    }

    /**
     * @test
     */
    public function publicPropertyForAccessibleMockObjectIsDirectlyAccessible()
    {
        self::assertSame(
            'This is a public property.',
            $this->accessibleMock->_get('publicProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectIsDirectlyAccessible()
    {
        self::assertSame(
            'This is a protected static property.',
            $this->accessibleMock->_getStatic('protectedStaticProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectCanBeSet()
    {
        $newValue = 'New value ' . microtime();
        $this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

        self::assertSame(
            $newValue,
            $this->accessibleMock->_getStatic('protectedStaticProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectCanSetOriginal()
    {
        $newValue = 'New value ' . microtime();
        $this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

        self::assertSame(
            $newValue,
            ProtectedClass::getStaticProperty()
        );
    }

    /**
     * @test
     */
    public function protectedMethodForAccessibleMockObjectIsDirectlyCallable()
    {
        self::assertTrue(
            $this->accessibleMock->_callRef('protectedMethod')
        );
    }

    /**
     * @test
     */
    public function publicMethodForAccessibleMockObjectIsDirectlyCallable()
    {
        self::assertTrue(
            $this->accessibleMock->_call('publicMethod')
        );
    }

    /**
     * @test
     */
    public function callCanPassEightParametersToMethod()
    {
        self::assertSame(
            '8: 1, 2, 3, 4, 5, 6, 7, 8',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8)
        );
    }

    /**
     * @test
     */
    public function callCanPassNineParametersToMethod()
    {
        self::assertSame(
            '9: 1, 2, 3, 4, 5, 6, 7, 8, 9',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9)
        );
    }

    /**
     * @test
     */
    public function callCanPassTenParametersToMethod()
    {
        self::assertSame(
            '10: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
        );
    }

    /**
     * @test
     */
    public function callRefCanCallMethodWithoutParameter()
    {
        self::assertSame(
            '0: ',
            $this->accessibleMock->_callRef(
                'argumentChecker'
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassOneParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '1: 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassTwoParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '2: 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassThreeParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '3: 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassFourParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '4: 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassFiveParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '5: 1, 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassSixParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '6: 1, 1, 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassSevenParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '7: 1, 1, 1, 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassEightParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '8: 1, 1, 1, 1, 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     */
    public function callRefCanPassNineParametersToMethod()
    {
        $parameter = 1;
        self::assertSame(
            '9: 1, 1, 1, 1, 1, 1, 1, 1, 1',
            $this->accessibleMock->_callRef(
                'argumentChecker',
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter,
                $parameter
            )
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function callRefForTenParametersThrowsException()
    {
        $this->accessibleMock->_callRef(
            'argumentChecker',
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter,
            $parameter
        );
    }
}
