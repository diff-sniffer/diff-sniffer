<?php declare(strict_types=1);

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
     * @return string
     * @throws Exception
     */
    public function getDiff() : string;

    /**
     * Returns contents of the given file
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function getContents(string $path) : string;
}
