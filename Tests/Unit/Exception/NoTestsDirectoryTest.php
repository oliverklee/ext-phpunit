<?php
namespace OliverKlee\Phpunit\Tests\Unit\Exception;

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
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class NoTestsDirectoryTest extends \Tx_Phpunit_TestCase
{
    /**
     * @test
     *
     * @expectedException \Tx_Phpunit_Exception_NoTestsDirectory
     *
     * @throws \Tx_Phpunit_Exception_NoTestsDirectory
     */
    public function exceptionCanBeThrown()
    {
        throw new \Tx_Phpunit_Exception_NoTestsDirectory('some message', 12345);
    }
}
