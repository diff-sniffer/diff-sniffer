<?php declare(strict_types=1);

namespace DiffSniffer\Git\ContentSource;

use DiffSniffer\Git\ContentSource;

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
        $path = $this->dir . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($path)) {
            return '';
        }

        return file_get_contents($path);
    }
}
