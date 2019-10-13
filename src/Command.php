<?php

declare(strict_types=1);

namespace DiffSniffer;

/**
 * CLI command interface
 */
interface Command
{
    /**
     * Returns human-readable command name
     */
    public function getName() : string;

    /**
     * Returns the name of the Composer package implementing the command
     */
    public function getPackageName() : string;

    /**
     * Returns command usage text
     *
     * @param string $programName CLI program name
     */
    public function getUsage(string $programName) : string;

    /**
     * Creates changeset from the arguments and removes the used ones leaving the rest for further processing
     *
     * @param array<int,string> $args Command arguments
     */
    public function createChangeSet(array &$args) : Changeset;
}
