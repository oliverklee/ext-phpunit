<?php
declare(strict_types = 1);

namespace OliverKlee\PhpUnit\Service;

/**
 * This class includes the necessary libraries.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class LibraryLoader
{
    /**
     * Includes PHPUnit and vfsStream.
     *
     * @return void
     */
    public static function includeAll(): void
    {
        require_once __DIR__ . '/../../Resources/Private/Php/vendor/autoload.php';
    }
}
