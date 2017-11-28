<?php declare(strict_types=1);

namespace DiffSniffer;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner as BaseRunner;

/**
 * CodeSniffer runner
 */
class Runner
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Runs CodeSniffer against specified changeset
     *
     * @param Changeset $changeset Changeset instance
     *
     * @return int
     */
    public function run(Changeset $changeset)
    {
        $diff = new Diff($changeset->getDiff());

        if (!count($diff)) {
            return 0;
        }

        $reporter = new Reporter($diff, $this->config);

        $runner = new BaseRunner();
        $runner->config = $this->config;
        $runner->reporter = $reporter;
        $runner->init();

        $it = new Iterator($diff, $changeset, $runner->ruleset, $this->config);

        /** @var File $file */
        foreach ($it as $file) {
            $runner->processFile($file);
        }

        $reporter->printReports();

        return (int) ($reporter->totalErrors || $reporter->totalWarnings);
    }
}
