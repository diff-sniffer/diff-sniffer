<?php declare(strict_types=1);

namespace DiffSniffer\Git;

/**
 * Diff source
 */
interface DiffSource
{
    /**
     * Returns contents of the file with the given path
     *
     * The diff will include only the files which have _added_ lines. We want to exclude the files which have only
     * removed ones in order to avoid parsing their diffs since there's nothing to check in them.
     *
     * This is useful for checking commits which remove a lot of files or a lot of lines in a lot of files.
     *
     * @return string
     */
    public function getDiff() : string;
}
