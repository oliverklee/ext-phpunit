<?php
declare(strict_types=1);

namespace OliverKlee\PhpUnit\Command;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Result printer for the Symfony console command.
 *
 * @author Felix Semmler <felix.semmler@aoe.com>
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @param string $buffer
     *
     * @return void
     */
    public function write($buffer)
    {
        $this->buildOutput();
        $this->output->write($buffer);
    }

    /**
     * @return void
     */
    private function buildOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }
    }
}
