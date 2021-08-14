<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit\Interfaces;

/**
 * This interface defines the methods provided by TestCase::getAccessibleMock.
 *
 * @author Nicole Cordes <nicole.cordes@googlemail.com>
 */
interface AccessibleObject
{
    // phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
    /**
     * Calls the method $method using call_user_func* and returns its return value.
     *
     * @param string $methodName name of method to call, must not be empty
     * @param mixed $arg1 first argument given to method $methodName
     * @param mixed $arg2 second argument given to method $methodName
     * @param mixed $arg3 third argument given to method $methodName
     * @param mixed $arg4 fourth argument given to method $methodName
     * @param mixed $arg5 fifth argument given to method $methodName
     * @param mixed $arg6 sixth argument given to method $methodName
     * @param mixed $arg7 seventh argument given to method $methodName
     * @param mixed $arg8 eighth argument given to method $methodName
     * @param mixed $arg9 ninth argument given to method $methodName
     *
     * @return mixed the return value from the method $methodName
     */
    public function _call(
        string $methodName,
        $arg1 = null,
        $arg2 = null,
        $arg3 = null,
        $arg4 = null,
        $arg5 = null,
        $arg6 = null,
        $arg7 = null,
        $arg8 = null,
        $arg9 = null
    );

    /**
     * Sets the value of a property.
     *
     * @param string $propertyName name of property to set value for, must not be empty
     * @param mixed $value the new value for the property defined in $propertyName
     *
     * @return void
     */
    public function _set(string $propertyName, $value): void;

    /**
     * Gets the value of the given property.
     *
     * @param string $propertyName name of property to return value of, must not be empty
     *
     * @return mixed the value of the property $propertyName
     */
    public function _get(string $propertyName);

    /**
     * Gets the value of the given static property.
     *
     * @param string $propertyName name of property to return value of, must not be empty
     *
     * @return mixed the value of the static property $propertyName
     */
    public function _getStatic(string $propertyName);
    // phpcs:enable
}
