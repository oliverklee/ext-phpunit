<?php
namespace OliverKlee\Phpunit\Command;

use PHPUnit_Framework_Exception;
use PHPUnit_TextUI_ResultPrinter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Result printer for Symfony console command.
 *
 * @author Felix Semmler <felix.semmler@aoe.com>
 */
class ResultPrinter extends PHPUnit_TextUI_ResultPrinter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor.
     *
     * @param mixed      $out
     * @param bool       $verbose
     * @param string     $colors
     * @param bool       $debug
     * @param int|string $numberOfColumns
     * @param bool       $reverse
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($out = null, $verbose = false, $colors = self::COLOR_DEFAULT, $debug = false, $numberOfColumns = 80, $reverse = false)
    {
        $this->output = new ConsoleOutput();

        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }

    public function write($buffer)
    {
        $this->output->write($buffer);
    }
}
