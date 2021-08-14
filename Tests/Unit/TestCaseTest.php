<?php

declare(strict_types=1);

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
     * @var ProtectedClass&\PHPUnit_Framework_MockObject_MockObject&AccessibleObject
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
        /** @var ProtectedClass&\PHPUnit_Framework_MockObject_MockObject&AccessibleObject $accessibleMock */
        $accessibleMock = $this->getAccessibleMock(ProtectedClass::class, null);
        $this->accessibleMock = $accessibleMock;
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
    public function setForEmptyPropertyNameInAccessibleMockObjectThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->accessibleMock->_set('', '');
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
        self::assertIsNotCallable([$this->protectedClassInstance, 'protectedMethod']);
    }

    /**
     * @test
     */
    public function publicMethodForFixtureIsDirectlyCallable(): void
    {
        self::assertIsCallable([$this->protectedClassInstance, 'publicMethod']);
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
        self::assertIsNotCallable([$this->mock, 'protectedMethod']);
    }

    /**
     * @test
     */
    public function publicMethodForMockObjectIsDirectlyCallable(): void
    {
        self::assertIsCallable([$this->mock, 'publicMethod']);
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
    public function protectedMethodForAccessibleMockObjectIsDirectlyCallable(): void
    {
        self::assertTrue(
            $this->accessibleMock->_call('protectedMethod')
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
    public function callWithTenParametersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1628955407);
        $this->expectExceptionMessage('_call currently only allows calls to methods with no more than 9 parameters.');

        // @phpstan-ignore-next-line We're testing a contract violation here on purpose.
        $this->accessibleMock->_call('argumentChecker', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
    }
}
