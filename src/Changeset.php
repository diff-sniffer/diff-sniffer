<?php

declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Changeset\Exception;

/**
 * Abstract changeset interface
 */
interface Changeset
{
    /**
     * Returns diff of the changeset
     *
     * @throws Exception
     */
    public function getDiff() : string;

    /**
     * Returns contents of the given file
     *
     * @throws Exception
     */
    public function getContents(string $path) : string;
}
