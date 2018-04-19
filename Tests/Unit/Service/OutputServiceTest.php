<?php
namespace OliverKlee\Phpunit\Tests\Unit\Service;

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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class OutputServiceTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Phpunit_Service_OutputService
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Phpunit_Service_OutputService();
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(
            SingletonInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function outputOutputsOutput()
    {
        $output = 'Hello world!';

        ob_start();
        $this->subject->output($output);

        self::assertSame(
            $output,
            ob_get_contents()
        );

        ob_end_clean();
    }
}
