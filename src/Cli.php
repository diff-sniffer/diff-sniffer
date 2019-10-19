<?php

declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Exception\RuntimeException;
use function array_map;
use function array_merge;
use function array_reduce;
use function assert;
use function error_get_last;
use function escapeshellcmd;
use function implode;
use function is_array;
use function is_resource;
use function proc_close;
use function proc_open;
use function stream_get_contents;

/**
 * CLI utility class
 */
final class Cli
{
    /**
     * Creates a shell command with given executable and arguments
     *
     * @param string            $cmd     Executable command
     * @param array<int,string> ...$args Command arguments
     */
    public function cmd(string $cmd, string ...$args) : string
    {
        return implode(' ', array_merge([
            escapeshellcmd($cmd),
        ], array_map('escapeshellarg', $args)));
    }

    /**
     * Joins commands by &&
     *
     * @param string[] ...$commands
     */
    public function and(string ...$commands) : string
    {
        return implode(' && ', $commands);
    }

    /**
     * Puts the given command in a sub-shell
     */
    public function subShell(string $command) : string
    {
        return '(' . $command . ')';
    }

    /**
     * Executes the specified command and returns its output or throws exception
     * containing error
     *
     * @param string      $cmd Command
     * @param string|null $cwd Current directory
     *
     * @throws RuntimeException
     */
    public function exec(string $cmd, ?string $cwd = null) : string
    {
        return $this->execPiped([$cmd], $cwd);
    }

    /**
     * Executes specified commands piped to each other and returns the resulting output or throws exception
     * containing error
     *
     * @param string[]    $commands Commands
     * @param string|null $cwd      Current directory
     *
     * @throws RuntimeException
     */
    public function execPiped(array $commands, ?string $cwd = null) : string
    {
        $spec      = ['pipe', 'r'];
        $stream    = null;
        $processes = $errorStreams = [];

        foreach ($commands as $command) {
            $processes[] = proc_open($command, [
                $stream ?? $spec,
                ['pipe', 'w'],
                ['pipe', 'w'],
            ], $pipes, $cwd);

            $stream         = $pipes[1];
            $errorStreams[] = $pipes[2];
        }

        assert(is_resource($stream));
        $output = stream_get_contents($stream);

        if ($output === false) {
            $error = error_get_last();
            assert(is_array($error));

            throw new RuntimeException($error['message']);
        }

        $errors = implode('', array_map(static function ($stream) {
            return stream_get_contents($stream);
        }, $errorStreams));

        $success = array_reduce($processes, static function ($success, $process) {
            return proc_close($process) === 0 && $success;
        }, true);

        if (! $success) {
            throw new RuntimeException($errors);
        }

        return $output;
    }
}
