<?php declare(strict_types=1);

namespace DiffSniffer\PreCommit;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Exception\RuntimeException;

/**
 * Changeset that represents Git staged area
 */
final class Changeset implements ChangesetInterface
{
    /**
     * CLI utilities
     *
     * @var Cli
     */
    private $cli;

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
     * @param string $cwd Current directory
     * @throws RuntimeException
     */
    public function __construct(Cli $cli, string $cwd)
    {
        $this->cli = $cli;

        $dir = $this->cli->exec(
            $this->cli->cmd('git', 'rev-parse', '--show-toplevel'),
            $cwd
        );

        $this->dir = rtrim($dir);
    }

    /**
     * {@inheritDoc}
     *
     * The diff will include only the files which have _added_ lines. We want to exclude the files which have only
     * removed ones in order to avoid parsing their diffs since there's nothing to check in them.
     *
     * This is useful for checking commits which remove a lot of files or a lot of lines in a lot of files.
     */
    public function getDiff() : string
    {
        return $this->cli->exec(
            $this->cli->pipe(
                $this->cli->cmd('git', 'diff', '--staged', '--numstat'),
                $this->cli->cmd('awk', '$1 == 0 { print ":!"$3 }'),
                $this->cli->cmd('xargs', 'git', 'diff', '--staged', '--', '.')
            ),
            $this->dir
        );
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
