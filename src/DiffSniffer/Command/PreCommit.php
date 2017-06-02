<?php

/**
 * Git pre-commit hook
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
namespace DiffSniffer\Command;

use DiffSniffer\Changeset;
use DiffSniffer\Changeset\Staged;
use DiffSniffer\Command;

/**
 * Git pre-commit hook
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
class PreCommit implements Command
{
    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return 'Diff Sniffer Pre-Commit Hook';
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageName() : string
    {
        return 'morozov/diff-sniffer-pre-commit';
    }

    /**
     * {@inheritDoc}
     */
    public function getUsage(string $programName) : string
    {
        return <<<USG
Usage: $programName [option]
Validate staged changes to correspond to the coding standards

USG;
    }

    /**
     * {@inheritDoc}
     */
    public function createChangeSet(array &$args) : Changeset
    {
        return new Staged(getcwd());
    }
}
