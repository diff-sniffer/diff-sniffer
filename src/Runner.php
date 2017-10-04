<?php

/**
 * CodeSniffer runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
namespace DiffSniffer;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner as BaseRunner;

/**
 * CodeSniffer runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
class Runner
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Runs CodeSniffer against specified changeset
     *
     * @param Changeset $changeset Changeset instance
     *
     * @return int
     */
    public function run(Changeset $changeset)
    {
        $diff = new Diff($changeset->getDiff());
        $reporter = new Reporter($diff, $this->config);

        $runner = new BaseRunner();
        $runner->config = $this->config;
        $runner->reporter = $reporter;
        $runner->init();

        $it = new Iterator($diff, $changeset, $runner->ruleset, $this->config);

        /** @var File $file */
        foreach ($it as $file) {
            $runner->processFile($file);
        }

        $reporter->printReports();

        return (int) ($reporter->totalErrors || $reporter->totalWarnings);
    }
}
