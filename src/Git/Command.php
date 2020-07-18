<?php

declare(strict_types=1);

namespace DiffSniffer\Git;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Cli;
use DiffSniffer\Command as CommandInterface;

use function array_shift;
use function array_unshift;
use function substr;

/**
 * Git command
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
    public function createChangeSet(array &$args): ChangesetInterface
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
