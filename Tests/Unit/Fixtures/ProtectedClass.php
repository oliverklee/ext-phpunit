<?php
namespace OliverKlee\Phpunit\Tests\Unit\Fixtures;

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
 * Test class.
 */
class ProtectedClass
{
    /**
     * @var string
     */
    protected $protectedProperty = 'This is a protected property.';

    /**
     * @var string
     */
    public $publicProperty = 'This is a public property.';

    /**
     * @var string
     */
    static protected $protectedStaticProperty = 'This is a protected static property.';

    /**
     * Protected test function which returns TRUE when processed.
     *
     * @return bool always TRUE
     */
    protected function protectedMethod()
    {
        return true;
    }

    /**
     * This function returns the number of passed arguments and their values.
     *
     * @return string a summary of the passed arguments, will not be empty
     */
    protected function argumentChecker()
    {
        return func_num_args() . ': ' . implode(', ', func_get_args());
    }

    /**
     * Public test function which returns TRUE when processed.
     *
     * @return bool always TRUE
     */
    public function publicMethod()
    {
        return true;
    }

    /**
     * Returns the content of the static property.
     *
     * @return string
     */
    public static function getStaticProperty()
    {
        return self::$protectedStaticProperty;
    }

    /**
     * Sets the content of the static property.
     *
     * @param string $value
     *
     * @return void
     */
    public static function setStaticProperty($value)
    {
        self::$protectedStaticProperty = $value;
    }
}
