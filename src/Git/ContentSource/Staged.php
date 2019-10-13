<?php declare(strict_types=1);

namespace DiffSniffer\Git\ContentSource;

use DiffSniffer\ContentSource;
use DiffSniffer\Cli;

/**
 * Staged content source
 */
class Staged implements ContentSource
{
    /**
     * @var Cli
     */
    private $cli;

    /**
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param Cli $cli
     * @param string $dir
     */
    public function __construct(Cli $cli, string $dir)
    {
        $this->cli = $cli;
        $this->dir = $dir;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $path) : string
    {
        return $this->cli->exec(
            $this->cli->cmd('git', 'show', ':' . $path),
            $this->dir
        );
    }
}
