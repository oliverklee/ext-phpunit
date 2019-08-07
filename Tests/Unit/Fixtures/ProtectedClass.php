<?php

namespace OliverKlee\Phpunit\Tests\Unit\Fixtures;

/**
 * Test class.
 */
class ProtectedClass
{
    /**
     * @var string
     */
    public $publicProperty = 'This is a public property.';

    /**
     * @var string
     */
    protected $protectedProperty = 'This is a protected property.';

    /**
     * @var string
     */
    private $privateProperty = 'This is a private property.';

    /**
     * @var string
     */
    protected static $protectedStaticProperty = 'This is a protected static property.';

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
