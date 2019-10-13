<?php

declare(strict_types=1);

namespace DiffSniffer\Git\PreCommit;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Cli;
use DiffSniffer\Command as CommandInterface;
use DiffSniffer\Git\Changeset;

/**
 * Git pre-commit hook
 *
 * @codeCoverageIgnore
 */
final class Command implements CommandInterface
{
    /** @var string */
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return 'Diff Sniffer Pre-Commit Hook';
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageName() : string
    {
        return 'diff-sniffer/git';
    }

    /**
     * {@inheritDoc}
     */
    public function getUsage(string $programName) : string
    {
        return <<<USG
Usage: $programName [option]

Validate staged changes for correspondence to the coding standard

USG;
    }

    /**
     * {@inheritDoc}
     */
    public function createChangeSet(array &$args) : ChangesetInterface
    {
        return new Changeset(new Cli(), ['--staged'], $this->directory);
    }
}
