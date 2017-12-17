<?php declare(strict_types=1);

namespace DiffSniffer\Git;

use DiffSniffer\Exception\RuntimeException;

/**
 * CLI utility class
 */
final class Cli
{
    /**
     * Creates a shell command with given executable and arguments
     *
     * @param string $cmd Executable command
     * @param string[] ...$args Command arguments
     * @return string
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
     * @return string
     */
    public function and(string ...$commands) : string
    {
        return implode(' && ', $commands);
    }

    /**
     * Puts the given command in a sub-shell
     *
     * @param string $command
     * @return string
     */
    public function subShell(string $command) : string
    {
        return '(' . $command . ')';
    }

    /**
     * Executes the specified command and returns its output or throws exception
     * containing error
     *
     * @param string $cmd Command
     * @param string|null $cwd Current directory
     *
     * @return string
     * @throws RuntimeException
     */
    public function exec(string $cmd, string $cwd = null) : string
    {
        return $this->execPiped([$cmd], $cwd);
    }

    /**
     * Executes specified commands piped to each other and returns the resulting output or throws exception
     * containing error
     *
     * @param string[] $commands Commands
     * @param string|null $cwd Current directory
     *
     * @return string
     * @throws RuntimeException
     */
    public function execPiped(array $commands, $cwd = null) : string
    {
        $pipedStream = ['pipe', 'r'];
        $processes = $errorStreams = [];

        foreach ($commands as $command) {
            $processes[] = proc_open($command, [
                $pipedStream,
                ['pipe', 'w'],
                ['pipe', 'w'],
            ], $pipes, $cwd);

            /** @var resource $pipedStream */
            $pipedStream = $pipes[1];
            $errorStreams[] = $pipes[2];
        }

        $output = stream_get_contents($pipedStream);

        $errors = implode('', array_map(function ($stream) {
            return stream_get_contents($stream);
        }, $errorStreams));

        $success = array_reduce($processes, function ($success, $process) {
            return proc_close($process) === 0 && $success;
        }, true);

        if (!$success) {
            throw new RuntimeException($errors);
        }

        return $output;
    }
}
