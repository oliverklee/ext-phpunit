<?php

declare(strict_types=1);

namespace OliverKlee\PhpUnit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI test runner Symfony console command.
 *
 * @author Helmut Hummel <helmut.hummel@typo3.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RunTestsCommand extends Command
{
    /**
     * Configures the command by defining the name, options and arguments.
     */
    public function configure(): void
    {
        $this
            ->setDescription('Runs PHPUnit tests from the command line.')
            ->setHelp('Call it like this: typo3/sysext/core/bin/typo3 phpunit:run --options="--verbose -c ..."')
            ->addOption(
                'options',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The complete options string passed to PHPUnit'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'The path of the tests to execute (deprecated)'
            );
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, an error code otherwise
     *
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \setlocale(LC_NUMERIC, 'C');

        // Store current TYPO3 configuration and set the default one.
        // This is needed as the configuration might include closures which cannot be backed-up.
        $globalBackup = $this->removeClosures($GLOBALS['TYPO3_CONF_VARS']);

        // run unit tests
        $runner = new \PHPUnit\TextUI\Command();
        $rawOptions = \is_string($input->getOption('options')) ? $input->getOption('options') : '';
        // The first array key is always ignored.
        $optionsForPhpunit = \array_merge([0 => ''], \explode(' ', $rawOptions));

        // set default printer only if no specific is set over CLI
        if (!\in_array('--printer', $optionsForPhpunit, true)) {
            $optionsForPhpunit[] = '--printer';
            $optionsForPhpunit[] = ResultPrinter::class;
        }
        if ($input->hasArgument('path')) {
            $optionsForPhpunit[] = $input->getArgument('path');
        }

        $result = $runner->run($optionsForPhpunit);

        // restore configuration
        $previousConfiguration = \is_array($GLOBALS['TYPO3_CONF_VARS']) ? $GLOBALS['TYPO3_CONF_VARS'] : [];
        $GLOBALS['TYPO3_CONF_VARS'] = \array_merge($previousConfiguration, $globalBackup);

        return $result;
    }

    /**
     * @param array<array-key, mixed> $variables
     *
     * @return array<array-key, mixed>
     */
    private function removeClosures(array &$variables): array
    {
        $backup = [];
        foreach ($variables as $key => &$value) {
            if (!\is_array($value) && !$value instanceof \Closure) {
                continue;
            }
            if (\is_array($value) && $value !== []) {
                $valueBackup = $this->removeClosures($value);
                if ($valueBackup !== []) {
                    $backup[$key] = $valueBackup;
                }
            } elseif ($value instanceof \Closure) {
                $backup[$key] =&$value;
                unset($variables[$key]);
            }
        }

        return $backup;
    }
}
