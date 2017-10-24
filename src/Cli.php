<?php declare(strict_types=1);

namespace DiffSniffer\PreCommit;

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
     * Pipes commands together
     *
     * @param string[] ...$commands
     * @return string
     */
    public function pipe(string ...$commands) : string
    {
        return implode(' | ', $commands);
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
        $process = proc_open(
            $cmd,
            array(
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            ),
            $pipes,
            $cwd
        );

        if (!$process) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Unable to start process "' . $cmd . '"');
            // @codeCoverageIgnoreEnd
        }

        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);

        $ret = proc_close($process);
        if ($ret != 0) {
            throw new RuntimeException($err, $ret);
        }

        return $out;
    }
}
