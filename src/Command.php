<?php

declare(strict_types=1);

namespace DiffSniffer;

/**
 * CLI command interface
 */
interface Command
{
    /**
     * Creates changeset from the arguments and removes the used ones leaving the rest for further processing
     *
     * @param array<int,string> $args Command arguments
     */
    public function createChangeSet(array &$args): Changeset;
}
