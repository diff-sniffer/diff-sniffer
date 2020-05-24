<?php

declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Command\Exception\BadUsage;
use Jean85\PrettyVersions;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use function array_merge;
use function array_shift;
use function assert;
use function basename;
use function current;
use function define;
use function getcwd;
use function is_string;
use function printf;
use const PHP_EOL;

define('PHP_CODESNIFFER_CBF', false);

/**
 * CLI application
 */
final class Application
{
    /**
     * Runs command
     *
     * @param array<int,string> $args Command line arguments
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
            $this->printUsage($programName);

            return 0;
        }

        if ($arg === '--version') {
            $this->printVersion();

            return 0;
        }

        try {
            $changeSet = $command->createChangeSet($args);
        } catch (BadUsage $e) {
            $this->printUsage($programName);

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
    private function printUsage(string $programName) : void
    {
        /** @lang text */
        echo <<<USG
Usage: $programName
       $programName --staged
       $programName <commit1> <commit2>

Validate changes for correspondence to the coding standard

PHP_CodeSniffer Options:

  See https://github.com/squizlabs/PHP_CodeSniffer/wiki/Usage

Miscellaneous Options:

  --help        Prints this usage information.
  --version     Prints the version and exits.

USG;
    }

    /**
     * Prints command version
     */
    private function printVersion() : void
    {
        $version = PrettyVersions::getVersion('diff-sniffer/diff-sniffer');

        printf(
            'Diff Sniffer version %s' . PHP_EOL,
            $version->getPrettyVersion()
        );
    }
}
