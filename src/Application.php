<?php

declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Command\Exception\BadUsage;
use Jean85\PrettyVersions;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use const PHP_EOL;
use function array_merge;
use function array_shift;
use function assert;
use function basename;
use function current;
use function define;
use function getcwd;
use function is_string;
use function printf;

define('PHP_CODESNIFFER_CBF', false);

/**
 * CLI application
 */
final class Application
{
    /**
     * Runs command
     *
     * @param Command           $command Command to run
     * @param array<int,string> $args    Command line arguments
     *
     * @return int Exit code
     *
     * @throws Exception
     */
    public function run(Command $command, array $args) : int
    {
        $programName = array_shift($args);
        assert(is_string($programName));
        $programName = basename($programName);

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

        $cwd = getcwd();
        assert(is_string($cwd));

        $loader = new ProjectLoader($cwd);
        $loader->registerClassLoader();

        $config = $loader->getPhpCodeSnifferConfiguration();

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
     */
    private function printUsage(Command $command, string $programName) : void
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
     */
    private function printVersion(Command $command) : void
    {
        $version = PrettyVersions::getVersion($command->getPackageName());

        printf(
            '%s version %s' . PHP_EOL,
            $command->getName(),
            $version->getPrettyVersion()
        );
    }
}
