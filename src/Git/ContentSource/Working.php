<?php

declare(strict_types=1);

namespace DiffSniffer\Git\ContentSource;

use DiffSniffer\ContentSource;
use DiffSniffer\Exception\RuntimeException;
use function assert;
use function error_get_last;
use function file_get_contents;
use function is_array;
use const DIRECTORY_SEPARATOR;

/**
 * Working content source
 */
class Working implements ContentSource
{
    /** @var string */
    private $dir;

    /**
     * Constructor
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function getContents(string $path) : string
    {
        $contents = file_get_contents($this->dir . DIRECTORY_SEPARATOR . $path);

        if ($contents === false) {
            $error = error_get_last();
            assert(is_array($error));

            throw new RuntimeException($error['message']);
        }

        return $contents;
    }
}
