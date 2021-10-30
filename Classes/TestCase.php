<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit;

/**
 * Base class for test cases.
 *
 * @deprecated will be removed in phpunit 9.x; directly use `\PHPUnit\Framework\TestCase` instead
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
}
