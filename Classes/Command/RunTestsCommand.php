<?php

namespace OliverKlee\Phpunit\Command;

use OliverKlee\Phpunit\Service\LibraryLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;

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
     *
     * @return void
     */
    public function configure()
    {
        $this
            ->setDescription('Runs PHPUnit tests from the command line.')
            ->setHelp('Call it like this: typo3/sysext/core/bin/typo3 phpunit:run --task=13 -f')
            ->setDefinition(
                [
                    new InputArgument('path', InputArgument::REQUIRED, 'The path of the tests to execute'),
                ]
            );
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        setlocale(LC_NUMERIC, 'C');

        // Make sure the _cli_ user is loaded
        Bootstrap::getInstance()->initializeBackendAuthentication();

        // Store current TYPO3 configuration and set the default one.
        // This is needed as the configuration might include closures which cannot be backed-up.
        $globalBackup = $this->removeClosures($GLOBALS['TYPO3_CONF_VARS']);

        LibraryLoader::includeAll();

        // Run unit tests
        $runner = new \PHPUnit_TextUI_Command();
        $result = (int)$runner->run(['test' => $input->getArgument('path')], true);

        // Restore configuration
        $GLOBALS['TYPO3_CONF_VARS'] = array_merge($GLOBALS['TYPO3_CONF_VARS'], $globalBackup);

        return $result;
    }

    /**
     * @param array $variables
     *
     * @return array
     */
    private function removeClosures(array &$variables)
    {
        $backup = [];
        foreach ($variables as $key => &$value) {
            if (!is_array($value) && !$value instanceof \Closure) {
                continue;
            }
            if (is_array($value) && !empty($value)) {
                $valueBackup = $this->removeClosures($value);
                if (!empty($valueBackup)) {
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
