<?php

namespace DiffSniffer;

use DiffSniffer\Command\Exception\BadUsage;
use PackageVersions\Versions;
use PHP_CodeSniffer\Config;

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

        define('PHP_CODESNIFFER_CBF', false);

        // workaround for an issue in Config: when $args are empty,
        // it takes values from the environment
        if (!count($args)) {
            $args = ['-d', 'error_reporting=' . error_reporting()];
        }

        $config = new Config($args);
        $runner = new Runner($config);

        return $runner->run($changeSet);
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
        printf(
            '%s version %s' . PHP_EOL,
            $command->getName(),
            $this->formatVersion(Versions::getVersion(
                $command->getPackageName()
            ))
        );
    }

    /**
     * Formats version string
     *
     * @param string $version
     * @return string
     */
    private function formatVersion(string $version) : string
    {
        list($version, $hash) = explode('@', $version);

        return preg_replace(
            '/(\.9999999)+-dev$/',
            '@' . preg_quote(substr($hash, 0, 7)),
            $version
        );
    }
}
