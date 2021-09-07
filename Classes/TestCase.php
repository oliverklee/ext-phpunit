<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit;

use OliverKlee\PhpUnit\Interfaces\AccessibleObject;

/**
 * This base class provides helper functions that might be convenient when testing in TYPO3.
 *
 * @author Robert Lemke <robert@typo3.org>
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Soren Soltveit <sso@systime.dk>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var bool
     */
    protected $backupStaticAttributes = false;

    /**
     * Creates a mock object which allows for calling protected methods and access of protected properties.
     *
     * @template M of object
     *
     * @param class-string<M> $originalClassName name of class to create the mock object of, must not be empty
     * @param string[]|null $methods names of the methods to mock, null for "mock no methods"
     * @param array<int, mixed> $arguments arguments to pass to constructor
     * @param string $mockClassName the class name to use for the mock class
     * @param bool $callOriginalConstructor whether to call the constructor
     * @param bool $callOriginalClone whether to call the __clone method
     *
     * @return \PHPUnit_Framework_MockObject_MockObject&AccessibleObject&M
     *         a mock of `$originalClassName` with access methods added
     *
     * @throws \InvalidArgumentException
     *
     * @deprecated will be removed in PHPUnit 8
     */
    protected function getAccessibleMock(
        string $originalClassName,
        ?array $methods = [],
        array $arguments = [],
        string $mockClassName = '',
        bool $callOriginalConstructor = true,
        bool $callOriginalClone = true
    ) {
        if ($originalClassName === '') {
            throw new \InvalidArgumentException('$originalClassName must not be empty.', 1334701880);
        }

        $mockBuilder = $this->getMockBuilder($this->buildAccessibleProxy($originalClassName))
            ->setMethods($methods)->setConstructorArgs($arguments)->setMockClassName($mockClassName);
        if (!$callOriginalConstructor) {
            $mockBuilder->disableOriginalConstructor();
        }
        if (!$callOriginalClone) {
            $mockBuilder->disableOriginalClone();
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject&AccessibleObject&M $mock */
        $mock = $mockBuilder->getMock();

        return $mock;
    }

    /**
     * Creates a proxy class of the specified class which allows for calling even protected methods and access of
     * protected properties.
     *
     * @template M of object
     *
     * @param class-string<M> $className name of class to make available, must not be empty
     *
     * @return class-string<M&AccessibleObject> fully-qualified name of the built class, will not be empty
     *
     * @deprecated will be removed in PHPUnit 8
     */
    protected function buildAccessibleProxy(string $className): string
    {
        /** @var class-string<M&AccessibleObject> $accessibleClassName */
        $accessibleClassName = \str_replace('.', '', \uniqid('Tx_Phpunit_AccessibleProxy', true));
        $class = new \ReflectionClass($className);
        $abstractModifier = $class->isAbstract() ? 'abstract ' : '';

        // phpcs:disable Squiz.PHP.Eval
        eval(
            $abstractModifier . 'class ' . $accessibleClassName .
            ' extends ' . $className . ' implements \\OliverKlee\\PhpUnit\\Interfaces\\AccessibleObject {' .
            'public function _call(string $methodName, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, ' .
            '$arg5 = null, $arg6 = null, $arg7 = null, $arg8 = null, $arg9 = null) {' .
            'if ($methodName === \'\') {' .
            'throw new \\InvalidArgumentException(\'$methodName must not be empty.\', 1334663993);' .
            '}' .
            'if (func_num_args() > 10) {' .
            'throw new \\InvalidArgumentException(' .
            '\'_call currently only allows calls to methods with no more than 9 parameters.\', 1628955407' .
            ');' .
            '}' .
            '$args = func_get_args();' .
            'return call_user_func_array(array($this, $methodName), array_slice($args, 1));' .
            '}' .
            'public function _set(string $propertyName, $value): void {' .
            'if ($propertyName === \'\') {' .
            'throw new \\InvalidArgumentException(\'$propertyName must not be empty.\', 1334664355);' .
            '}' .
            '$this->$propertyName = $value;' .
            '}' .
            'public function _get(string $propertyName) {' .
            'if ($propertyName === \'\') {' .
            'throw new \\InvalidArgumentException(\'$propertyName must not be empty.\', 1334664967);' .
            '}' .
            'return $this->$propertyName;' .
            '}' .
            'public function _getStatic(string $propertyName) {' .
            'if ($propertyName === \'\') {' .
            'throw new \\InvalidArgumentException(\'$propertyName must not be empty.\', 1344242603);' .
            '}' .
            'return self::$$propertyName;' .
            '}' .
            '}'
        );
        // phpcs:enable

        return $accessibleClassName;
    }
}
