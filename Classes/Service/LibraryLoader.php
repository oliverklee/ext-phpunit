<?php

namespace OliverKlee\PhpUnit\Service;

/**
 * This class includes the necessary libraries.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class LibraryLoader
{
    /**
     * Includes PHPUnit, Selenium and vfsStream.
     *
     * @return void
     */
    public static function includeAll()
    {
        require_once PATH_site . 'typo3conf/ext/phpunit/Resources/Private/Php/vendor/autoload.php';
    }
}
