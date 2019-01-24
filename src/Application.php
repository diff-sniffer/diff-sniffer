<?php declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Command\Exception\BadUsage;
use Jean85\PrettyVersions;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;

define('PHP_CODESNIFFER_CBF', false);

/**
 * CLI application
 */
final class Application
{
    /**
     * Runs command
     *
     * @param Command $command Command to run
     * @param array $args Command line arguments
     * @return int Exit code
     */
    public function run(Command $command, array $args) : int
    {
        $programName = basename(array_shift($args));

        $arg = current($args);

        if ($arg === '--help') {
            $this->printUsage($command, $programName);
            return 0;
        }

        if ($arg === '--version') {
            $this->printVersion($command);
            return 0;
        }

        try {
            $changeSet = $command->createChangeSet($args);
        } catch (BadUsage $e) {
            $this->printUsage($command, $programName);
            return 1;
        }

        $config = (new ConfigLoader())->loadConfig(getcwd());

        try {
            if ($config !== null) {
                foreach ($config as $key => $value) {
                    Config::setConfigData($key, $value, true);
                }
            }

            // pass configuration using CLI arguments to override what's defined in the rule set
            $config = new Config(array_merge($args, ['--no-cache']));
            $runner = new Runner($config);

            return $runner->run($changeSet);
        } catch (DeepExitException $e) {
            echo $e->getMessage();
            return $e->getCode();
        }
    }

    /**
     * Prints command usage text
     *
     * @param Command $command
     * @param string $programName
     */
    private function printUsage(Command $command, string $programName)
    {
        echo $command->getUsage($programName);
        echo PHP_EOL;

        echo <<<HLP
PHP_CodeSniffer Options:

  See https://github.com/squizlabs/PHP_CodeSniffer/wiki/Usage

Miscellaneous Options:

  --help        Prints this usage information.
  --version     Prints the version and exits.

HLP;
    }

    /**
     * Prints command version
     *
     * @param Command $command
     */
    private function printVersion(Command $command)
    {
        $version = PrettyVersions::getVersion($command->getPackageName());

        printf(
            '%s version %s' . PHP_EOL,
            $command->getName(),
            $version->getPrettyVersion()
        );
    }
}
