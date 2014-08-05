<?php

/**
 * CodeSniffer pre-commit mode runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer\Runner
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2014 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-pre-commit
 */
namespace DiffSniffer\Runner;

use \DiffSniffer\Runner as PlainRunner;
use \DiffSniffer\Changeset\Staged as Changeset;

/**
 * CodeSniffer pre-commit mode runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer\Runner
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2014 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-pre-commit
 */
class Staged
{
    /**
     * Runner instance
     *
     * @var PlainRunner
     */
    protected $runner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->runner = new PlainRunner();
    }

    /**
     * Runs CodeSniffer in pre-commit mode against specified directory
     *
     * @param string $cwd       Current directory
     * @param array  $arguments PHP_CodeSniffer command line arguments
     *
     * @return int
     */
    public function run($cwd, $arguments)
    {
        $changeset = new Changeset($cwd);
        return $this->runner->run($changeset, $arguments);
    }
}
