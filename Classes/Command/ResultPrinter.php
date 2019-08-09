<?php
declare(strict_types = 1);

namespace OliverKlee\PhpUnit\Command;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Result printer for the Symfony console command.
 *
 * @author Felix Semmler <felix.semmler@aoe.com>
 */
class ResultPrinter extends \PHPUnit\TextUI\ResultPrinter
{
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @param string $buffer
     */
    public function write(string $buffer): void
    {
        $this->buildOutput();
        $this->output->write($buffer);
    }

    private function buildOutput(): void
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }
    }
}
