<?php
declare(strict_types = 1);

namespace OliverKlee\PhpUnit\Tests\Unit\Fixtures;

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
     * Protected test function which returns true when processed.
     *
     * @return bool always true
     */
    protected function protectedMethod(): bool
    {
        return true;
    }

    /**
     * This function returns the number of passed arguments and their values.
     *
     * @return string a summary of the passed arguments, will not be empty
     */
    protected function argumentChecker(): string
    {
        return \func_num_args() . ': ' . \implode(', ', \func_get_args());
    }

    /**
     * Public test function which returns true when processed.
     *
     * @return bool always true
     */
    public function publicMethod(): bool
    {
        return true;
    }

    /**
     * Returns the content of the static property.
     *
     * @return string
     */
    public static function getStaticProperty(): string
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
    public static function setStaticProperty(string $value): void
    {
        self::$protectedStaticProperty = $value;
    }
}
