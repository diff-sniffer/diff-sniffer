<?php

namespace DiffSniffer\Tests;

use DiffSniffer\Changeset;
use const DIRECTORY_SEPARATOR;

/**
 * Fixture changeset
 */
class FixtureChangeset implements Changeset
{
    /**
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param string $dir The directory where the changeset is located
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritDoc}
     */
    public function getDiff() : string
    {
        return file_get_contents($this->dir . DIRECTORY_SEPARATOR . 'changeset.diff');
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $path) : string
    {
        return file_get_contents($this->dir . '/tree/' . $path);
    }
}
