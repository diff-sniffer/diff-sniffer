<?php

declare(strict_types=1);

namespace DiffSniffer\Git\Range;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Cli;
use DiffSniffer\Command as CommandInterface;
use DiffSniffer\Git\Changeset;
use function array_shift;
use function array_unshift;
use function substr;

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
        return 'Diff Sniffer for Git';
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
        /** @lang text */
        return <<<USG
Usage: $programName
       $programName --staged
       $programName <commit1> <commit2>

Validate changes for correspondence to the coding standard

USG;
    }

    /**
     * {@inheritDoc}
     */
    public function createChangeSet(array &$args) : ChangesetInterface
    {
        $diffArgs = [];

        while (true) {
            $arg = array_shift($args);

            if ($arg === null) {
                break;
            }

            if (substr($arg, 0, 1) === '-' && $arg !== '--staged') {
                array_unshift($args, $arg);
                break;
            }

            $diffArgs[] = $arg;
        }

        return new Changeset(new Cli(), $diffArgs, $this->directory);
    }
}
