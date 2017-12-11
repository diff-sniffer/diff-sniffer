<?php declare(strict_types=1);

namespace DiffSniffer\Git;

/**
 * Diff source
 */
final class DiffSource
{
    /**
     * CLI utilities
     *
     * @var Cli
     */
    private $cli;

    /**
     * @var string[]
     */
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
     * @param Cli $cli CLI utilities
     * @param array $args
     * @param string $dir
     */
    public function __construct(Cli $cli, array $args, string $dir)
    {
        $this->cli = $cli;
        $this->args = $args;
        $this->dir = $dir;
    }

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
    public function getDiff() : string
    {
        return $this->cli->exec(
            $this->cli->pipe(
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
                $this->cli->cmd('xargs', 'git', 'diff', ...array_merge($this->args, [
                    '--',
                ]))
            ),
            $this->dir
        );
    }
}
