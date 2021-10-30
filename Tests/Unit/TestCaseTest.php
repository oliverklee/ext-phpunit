<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit\Tests\Unit;

use OliverKlee\PhpUnit\TestCase;

/**
 * @covers \OliverKlee\PhpUnit\TestCase
 */
class TestCaseTest extends TestCase
{
    /**
     * @test
     */
    public function isSubclassOfFrameworkTestCase(): void
    {
        self::assertInstanceOf(\PHPUnit\Framework\TestCase::class, $this);
    }
}
