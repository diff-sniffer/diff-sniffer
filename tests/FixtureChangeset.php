<?php

declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\Changeset;
use const DIRECTORY_SEPARATOR;
use function assert;
use function file_get_contents;
use function is_string;

/**
 * Fixture changeset
 */
class FixtureChangeset implements Changeset
{
    /** @var string */
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
        return $this->getFileContents($this->dir . DIRECTORY_SEPARATOR . 'changeset.diff');
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $path) : string
    {
        return $this->getFileContents($this->dir . '/tree/' . $path);
    }

    private function getFileContents(string $path) : string
    {
        $contents = file_get_contents($path);
        assert(is_string($contents));

        return $contents;
    }
}
