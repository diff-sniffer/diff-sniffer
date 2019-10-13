<?php

declare(strict_types=1);

namespace DiffSniffer\Git;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Cli;
use DiffSniffer\ContentSource;
use DiffSniffer\DiffSource;
use DiffSniffer\Exception\RuntimeException;
use DiffSniffer\Git\ContentSource\Commit;
use DiffSniffer\Git\ContentSource\Staged;
use DiffSniffer\Git\ContentSource\Working;
use DiffSniffer\Git\DiffSource\Unix;
use DiffSniffer\Git\DiffSource\Windows;
use function count;
use function defined;
use function in_array;
use function preg_match;
use function rtrim;

/**
 * Changeset that represents Git staged area
 */
final class Changeset implements ChangesetInterface
{
    /** @var DiffSource */
    private $diffSource;

    /** @var ContentSource */
    private $contentSource;

    /**
     * Constructor
     *
     * @param Cli               $cli  CLI utilities
     * @param array<int,string> $args
     *
     * @throws RuntimeException
     */
    public function __construct(Cli $cli, array $args, string $dir)
    {
        $dir = $cli->exec(
            $cli->cmd('git', 'rev-parse', '--show-toplevel'),
            $dir
        );

        $dir = rtrim($dir);

        $this->diffSource    = $this->getDiffSource($cli, $args, $dir);
        $this->contentSource = $this->getContentSource($cli, $args, $dir);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiff() : string
    {
        return $this->diffSource->getDiff();
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $path) : string
    {
        return $this->contentSource->getContents($path);
    }

    /**
     * Creates the content source corresponding to the given `git diff` arguments
     *
     * @param array<int,string> $args
     */
    private function getDiffSource(Cli $cli, array $args, string $dir) : DiffSource
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return new Windows($cli, $args, $dir);
        }

        return new Unix($cli, $args, $dir);
    }

    /**
     * Creates the content source corresponding to the given `git diff` arguments
     *
     * @param array<int,string> $args
     */
    private function getContentSource(Cli $cli, array $args, string $dir) : ContentSource
    {
        if (in_array('--staged', $args, true)) {
            return new Staged($cli, $dir);
        }

        if (count($args) === 0) {
            return new Working($dir);
        }

        if (count($args) === 1) {
            $arg = $args[0];

            if (preg_match('/\.{2,3}([^.].*)?$/', $arg, $matches)) {
                $ref = 'HEAD';

                if (isset($matches[1])) {
                    $ref = $matches[1];
                }

                $args[] = $ref;
            }
        }

        if (count($args) > 1) {
            return new Commit($cli, $dir, $args[1]);
        }

        return new Working($dir);
    }
}
