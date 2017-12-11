<?php declare(strict_types=1);

namespace DiffSniffer\Git;

/**
 * Content source
 */
interface ContentSource
{
    /**
     * Returns contents of the file with the given path
     *
     * @param string $path
     * @return string
     */
    public function getContents(string $path) : string;
}
