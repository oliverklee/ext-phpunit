<?php
declare(strict_types = 1);

namespace OliverKlee\PhpUnit\Tests\Unit;

use OliverKlee\PhpUnit\Interfaces\AccessibleObject;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\PhpUnit\Tests\Unit\Fixtures\ProtectedClass;

/**
 * Test case.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestCaseTest extends TestCase
{
    /**
     * @var ProtectedClass
     */
    private $protectedClassInstance = null;

    /**
     * @var ProtectedClass|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mock = null;

    /**
     * @var ProtectedClass|\PHPUnit_Framework_MockObject_MockObject|AccessibleObject
     */
    private $accessibleMock = null;

    /**
     * Backup of static property of the accessible mock.
     *
     * @var string
     */
    private $staticProperty = '';

    protected function setUp(): void
    {
        $this->protectedClassInstance = new ProtectedClass();
        $this->mock = $this->createMock(ProtectedClass::class);
        $this->accessibleMock = $this->getAccessibleMock(ProtectedClass::class, null);
        $this->staticProperty = ProtectedClass::getStaticProperty();
    }

    protected function tearDown(): void
    {
        ProtectedClass::setStaticProperty($this->staticProperty);
    }

    /**
     * @test
     */
    public function getAccessibleMockWithEmptyClassNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getAccessibleMock('');
    }

    /**
     * @test
     */
    public function callForEmptyMethodNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_call('');
    }

    /**
     * @test
     */
    public function callRefForEmptyMethodNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_callRef('');
    }

    /**
     * @test
     */
    public function setForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_set('', '');
    }

    /**
     * @test
     */
    public function setRefForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $value = '';

        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_setRef('', $value);
    }

    /**
     * @test
     */
    public function setStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_setStatic('', '');
    }

    /**
     * @test
     */
    public function getForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_get('');
    }

    /**
     * @test
     */
    public function getStaticForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_getStatic('');
    }

    /**
     * @test
     */
    public function protectedPropertyForFixtureIsNotDirectlyAccessible(): void
    {
        self::assertNotContains('protectedProperty', \get_object_vars($this->protectedClassInstance));
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForFixtureIsNotDirectlyAccessible(): void
    {
        self::assertArrayNotHasKey(
            'protectedStaticProperty',
            \get_class_vars(\get_class($this->protectedClassInstance))
        );
    }

    /**
     * @test
     */
    public function publicPropertyForFixtureIsDirectlyAccessible(): void
    {
        self::assertSame(
            'This is a public property.',
            $this->protectedClassInstance->publicProperty
        );
    }

    /**
     * @test
     */
    public function protectedMethodForFixtureIsNotDirectlyCallable(): void
    {
        self::assertNotInternalType(
            'callable',
            [$this->protectedClassInstance, 'protectedMethod']
        );
    }

    /**
     * @test
     */
    public function publicMethodForFixtureIsDirectlyCallable(): void
    {
        self::assertInternalType(
            'callable',
            [$this->protectedClassInstance, 'publicMethod']
        );
    }

    /**
     * @test
     */
    public function protectedPropertyForMockObjectIsNotDirectlyAccessible(): void
    {
        self::assertNotContains('protectedProperty', \get_object_vars($this->mock));
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForMockObjectIsNotDirectlyAccessible(): void
    {
        self::assertArrayNotHasKey(
            'protectedStaticProperty',
            \get_class_vars(\get_class($this->mock))
        );
    }

    /**
     * @test
     */
    public function publicPropertyForMockObjectIsDirectlyAccessible(): void
    {
        self::assertSame(
            'This is a public property.',
            $this->mock->publicProperty
        );
    }

    /**
     * @test
     */
    public function protectedMethodForMockObjectIsNotDirectlyCallable(): void
    {
        self::assertNotInternalType(
            'callable',
            [$this->mock, 'protectedMethod']
        );
    }

    /**
     * @test
     */
    public function publicMethodForMockObjectIsDirectlyCallable(): void
    {
        self::assertInternalType(
            'callable',
            [$this->mock, 'publicMethod']
        );
    }

    /**
     * @test
     */
    public function protectedPropertyForAccessibleMockObjectIsDirectlyAccessible(): void
    {
        self::assertSame(
            'This is a protected property.',
            $this->accessibleMock->_get('protectedProperty')
        );
    }

    /**
     * @test
     */
    public function publicPropertyForAccessibleMockObjectIsDirectlyAccessible(): void
    {
        self::assertSame(
            'This is a public property.',
            $this->accessibleMock->_get('publicProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectIsDirectlyAccessible(): void
    {
        self::assertSame(
            'This is a protected static property.',
            $this->accessibleMock->_getStatic('protectedStaticProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectCanBeSet(): void
    {
        $newValue = 'New value ' . \microtime();
        $this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

        self::assertSame(
            $newValue,
            $this->accessibleMock->_getStatic('protectedStaticProperty')
        );
    }

    /**
     * @test
     */
    public function protectedStaticPropertyForAccessibleMockObjectCanSetOriginal(): void
    {
        $newValue = 'New value ' . \microtime();
        $this->accessibleMock->_setStatic('protectedStaticProperty', $newValue);

        self::assertSame(
            $newValue,
            ProtectedClass::getStaticProperty()
        );
    }

    /**
     * @test
     */
    public function protectedMethodForAccessibleMockObjectIsDirectlyCallable(): void
    {
        self::assertTrue(
            $this->accessibleMock->_callRef('protectedMethod')
        );
    }

    /**
     * @test
     */
    public function publicMethodForAccessibleMockObjectIsDirectlyCallable(): void
    {
        self::assertTrue(
            $this->accessibleMock->_call('publicMethod')
        );
    }

    /**
     * @test
     */
    public function callCanPassEightParametersToMethod(): void
    {
        self::assertSame(
            '8: 1, 2, 3, 4, 5, 6, 7, 8',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8)
        );
    }

    /**
     * @test
     */
    public function callCanPassNineParametersToMethod(): void
    {
        self::assertSame(
            '9: 1, 2, 3, 4, 5, 6, 7, 8, 9',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9)
        );
    }

    /**
     * @test
     */
    public function callCanPassTenParametersToMethod(): void
    {
        self::assertSame(
            '10: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10',
            $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
        );
    }

    /**
     * @test
     */
    public function callRefCanCallMethodWithoutParameter(): void
    {
        self::assertSame(
            '0: ',
            $this->accessibleMock->_callRef('argumentChecker')
        );
    }

    /**
     * @test
     */
    public function callRefCanPassOneParametersToMethod(): void
    {
        $parameter = 1;
        self::assertSame(
            '1: 1',
            $this->accessibleMock->_callRef('argumentChecker', $parameter)
        );
    }

    /**
     * @test
     */
    public function callRefCanPassTwoParametersToMethod(): void
    {
        $parameter = 1;
        self::assertSame(
            '2: 1, 1',
            $this->accessibleMock->_callRef('argumentChecker', $parameter, $parameter)
        );
    }

    /**
     * @test
     */
    public function callRefCanPassThreeParametersToMethod(): void
    {
        $parameter = 1;
        self::assertSame(
            '3: 1, 1, 1',
            $this->accessibleMock->_callRef('argumentChecker', $parameter, $parameter, $parameter)
        );
    }

    /**
     * @test
     */
    public function callRefCanPassFourParametersToMethod(): void
    {
        $parameter = 1;
        self::assertSame(
            '4: 1, 1, 1, 1',
            $this->accessibleMock->_callRef('argumentChecker', $parameter, $parameter, $parameter, $parameter)
        );
    }

    /**
     * @test
     */
    public function callRefCanPassFiveParametersToMethod(): void
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
    public function callRefCanPassSixParametersToMethod(): void
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
    public function callRefCanPassSevenParametersToMethod(): void
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
    public function callRefCanPassEightParametersToMethod(): void
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
    public function callRefCanPassNineParametersToMethod(): void
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
     */
    public function callRefForTenParametersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

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

    /**
     * @test
     */
    public function getProtectedPropertyReturnsValueOfPublicProperty(): void
    {
        $object = new ProtectedClass();
        $result = $this->getProtectedProperty($object, 'publicProperty');

        self::assertSame('This is a public property.', $result);
    }

    /**
     * @test
     */
    public function getProtectedPropertyReturnsValueOfProtectedProperty(): void
    {
        $object = new ProtectedClass();
        $result = $this->getProtectedProperty($object, 'protectedProperty');

        self::assertSame('This is a protected property.', $result);
    }

    /**
     * @test
     */
    public function getProtectedPropertyReturnsValueOfPrivateProperty(): void
    {
        $object = new ProtectedClass();
        $result = $this->getProtectedProperty($object, 'privateProperty');

        self::assertSame('This is a private property.', $result);
    }

    /**
     * @test
     */
    public function getProtectedPropertyForEmptyPropertyNameThrowsException(): void
    {
        $this->expectException(\ReflectionException::class);

        $object = new ProtectedClass();
        $result = $this->getProtectedProperty($object, '');

        self::assertSame('This is a private property.', $result);
    }

    /**
     * @test
     */
    public function getProtectedPropertyForInexistentPropertyThrowsException(): void
    {
        $this->expectException(\ReflectionException::class);

        $object = new ProtectedClass();
        $result = $this->getProtectedProperty($object, 'doesNotExist');

        self::assertSame('This is a private property.', $result);
    }

    /**
     * @return array[]
     */
    public function nonObjectDataProvider(): array
    {
        return [
            'null' => [null],
            'bool' => [true],
            'int' => [1],
            'float' => [3.14159],
            'string' => ['Hello!'],
        ];
    }

    /**
     * @test
     *
     * @param mixed $nonObject
     *
     * @dataProvider nonObjectDataProvider
     */
    public function getProtectedPropertyOnNonObjectThrowsException($nonObject): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $result = $this->getProtectedProperty($nonObject, 'someProperty');

        self::assertSame('This is a private property.', $result);
    }
}
