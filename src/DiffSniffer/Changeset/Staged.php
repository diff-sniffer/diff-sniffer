<?php

/**
 * Changeset that represents Git staged area
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2014 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-pre-commit
 */
namespace DiffSniffer\Changeset;

use DiffSniffer\Changeset;

/**
 * Changeset that represents Git staged area
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-pre-commit
 */
class Staged implements Changeset
{
    /**
     * Git working directory
     *
     * @var string
     */
    private $dir;

    /**
     * Constructor
     *
     * @param string $cwd Current directory
     * @throws Exception
     */
    public function __construct(string $cwd)
    {
        $dir = $this->exec(
            $this->cmd('git', 'rev-parse', '--show-toplevel'),
            $cwd
        );

        $this->dir = rtrim($dir);
    }

    /**
     * {@inheritDoc}
     */
    public function getDiff() : string
    {
        return $this->exec(
            implode(' | ', [
                $this->cmd('git', 'diff', '--staged', '--numstat'),
                $this->cmd('grep', '-vP', '^0\\t'),
                $this->cmd('cut', '-f3'),
                $this->cmd('xargs', 'git', 'diff', '--staged', '--'),
            ]),
            $this->dir
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getContents(string $path) : string
    {
        return $this->exec(
            $this->cmd('git', 'show', ':' . $path),
            $this->dir
        );
    }

    /**
     * Executes specified command and returns its output or throws exception
     * containing error
     *
     * @param string $cmd Command
     * @param string $cwd Current directory
     *
     * @return string
     * @throws Exception
     */
    private function exec(string $cmd, $cwd)
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
            throw new Exception('Unable to start process "' . $cmd . '"');
        }

        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);

        $ret = proc_close($process);
        if ($ret != 0) {
            throw new Exception($err, $ret);
        }

        return $out;
    }

    private function cmd(string $cmd, string ...$args) : string
    {
        return implode(' ', array_merge([
            escapeshellcmd($cmd),
        ], array_map('escapeshellarg', $args)));
    }
}
