<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit;

/**
 * Base class for test cases.
 *
 * @deprecated will be removed in phpunit 9.x; directly use `\PHPUnit\Framework\TestCase` instead
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool|null
     */
    protected $backupGlobals = false;

    /**
     * @var bool|null
     */
    protected $backupStaticAttributes = false;
}
