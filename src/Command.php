<?php declare(strict_types=1);

namespace DiffSniffer\Git;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Command as CommandInterface;

/**
 * Git pre-commit hook
 *
 * @codeCoverageIgnore
 */
final class Command implements CommandInterface
{
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
        return 'morozov/diff-sniffer-pre-commit';
    }

    /**
     * {@inheritDoc}
     */
    public function getUsage(string $programName) : string
    {
        return <<<USG
Usage: $programName [option]
Validate staged changes to correspond to the coding standards

USG;
    }

    /**
     * {@inheritDoc}
     */
    public function createChangeSet(array &$args) : ChangesetInterface
    {
        return new Changeset(new Cli(), getcwd());
    }
}
