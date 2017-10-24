<?php

namespace DiffSniffer;

/**
 * CLI command interface
 */
interface Command
{
    /**
     * Returns human-readable command name
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Returns the name of the Composer package implementing the command
     *
     * @return string
     */
    public function getPackageName() : string;

    /**
     * Returns command usage text
     *
     * @param string $programName CLI program name
     * @return string
     */
    public function getUsage(string $programName) : string;

    /**
     * Creates changeset from the arguments and removes the used ones leaving the rest for further processing
     *
     * @param string[] $args Command arguments
     * @return Changeset
     */
    public function createChangeSet(array &$args) : Changeset;
}
