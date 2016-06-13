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
 * @copyright 2014 Sergei Morozov
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
    protected $gitDir;

    /**
     * Constructor
     *
     * @param string $cwd Current directory
     */
    public function __construct($cwd)
    {
        $cmd = 'git rev-parse --show-toplevel';
        $gitDir = $this->exec($cmd, $cwd);
        $this->gitDir = rtrim($gitDir);
    }

    /**
     * Returns diff of the changeset
     *
     * @return string
     * @throws Exception
     */
    public function getDiff()
    {
        $cmd = $this->getFilesCommand() . ' | xargs git diff --staged --';
        return $this->exec($cmd, $this->gitDir);
    }

    /**
     * Exports the changed files into specified directory
     *
     * @param string $dir Target directory
     *
     * @return void
     * @throws Exception
     */
    public function export($dir)
    {
        $cmd = $this->getFilesCommand() . ' | xargs git checkout-index --prefix="' . $dir . '/" --';
        $this->exec($cmd, $this->gitDir);
    }

    /**
     * Returns the sub-command which generates the list of files to be checked
     *
     * @return string
     */
    private function getFilesCommand()
    {
        // produce the list of files which have added linens in the staging area
        return 'git diff --staged --numstat | grep -vP "^0\\t" | cut -f3';
    }

    /**
     * Executes specified command and returns its output or throws exception
     * containing error
     * 
     * @param string      $cmd Command to execute
     * @param string|null $cwd Current directory
     *
     * @return string
     * @throws Exception
     */
    protected function exec($cmd, $cwd = null)
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
}
