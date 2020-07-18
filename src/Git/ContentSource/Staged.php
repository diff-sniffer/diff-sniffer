<?php

declare(strict_types=1);

namespace DiffSniffer\Git\ContentSource;

use DiffSniffer\Cli;
use DiffSniffer\ContentSource;

/**
 * Staged content source
 */
class Staged implements ContentSource
{
    /** @var Cli */
    private $cli;

    /** @var string */
    private $dir;

    /**
     * Constructor
     */
    public function __construct(Cli $cli, string $dir)
    {
        $this->cli = $cli;
        $this->dir = $dir;
    }

    public function getContents(string $path): string
    {
        return $this->cli->exec(
            $this->cli->cmd('git', 'show', ':' . $path),
            $this->dir
        );
    }
}
