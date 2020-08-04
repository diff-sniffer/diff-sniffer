<?php

declare(strict_types=1);

namespace DiffSniffer;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use PHP_CodeSniffer\Runner as BaseRunner;

use function count;

/**
 * CodeSniffer runner
 */
class Runner
{
    /** @var Config */
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
     * @throws DeepExitException
     * @throws Exception
     */
    public function run(Changeset $changeset, string $cwd): int
    {
        $diff = new Diff($changeset->getDiff());

        if (! count($diff)) {
            return 0;
        }

        $reporter = new Reporter($diff, $this->config, $cwd);

        $runner           = new BaseRunner();
        $runner->config   = $this->config;
        $runner->reporter = $reporter;
        $runner->init();

        $it = new Iterator($diff, $changeset, $runner->ruleset, $this->config, $cwd);

        foreach ($it as $file) {
            $runner->processFile($file);
        }

        $reporter->printReports();

        return (int) ($reporter->totalErrors || $reporter->totalWarnings);
    }
}
