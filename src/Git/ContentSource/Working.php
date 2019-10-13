<?php declare(strict_types=1);

namespace DiffSniffer\Git\ContentSource;

use DiffSniffer\Exception\RuntimeException;
use DiffSniffer\ContentSource;

/**
 * Working content source
 */
class Working implements ContentSource
{
    /**
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * {@inheritDoc}
     */
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
