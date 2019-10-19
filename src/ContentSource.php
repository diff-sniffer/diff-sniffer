<?php

declare(strict_types=1);

namespace DiffSniffer;

/**
 * Content source
 */
interface ContentSource
{
    /**
     * Returns contents of the file with the given path
     */
    public function getContents(string $path) : string;
}
