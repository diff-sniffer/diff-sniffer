<?php

declare(strict_types=1);

namespace DiffSniffer\Git\DiffSource;

use DiffSniffer\Cli;
use DiffSniffer\DiffSource;

use function array_merge;

/**
 * Unix-specific implementation of the diff source
 */
class Unix implements DiffSource
{
    /**
     * CLI utilities
     *
     * @var Cli
     */
    private $cli;

    /** @var array<int,string> */
    private $args;

    /**
     * Git working directory
     *
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param Cli               $cli  CLI utilities
     * @param array<int,string> $args
     */
    public function __construct(Cli $cli, array $args, string $dir)
    {
        $this->cli  = $cli;
        $this->args = $args;
        $this->dir  = $dir;
    }

    public function getDiff(): string
    {
        return $this->cli->execPiped([
            $this->cli->cmd('git', 'diff', ...array_merge($this->args, [
                '--numstat',
                '--',
            ])),
            $this->cli->subShell(
                $this->cli->and(
                    $this->cli->cmd('echo', '.'),
                    $this->cli->cmd('awk', '$1 == 0 { print ":!"$3 }')
                )
            ),
            $this->cli->cmd('xargs', 'git', 'diff', ...array_merge($this->args, ['--'])),
        ], $this->dir);
    }
}
