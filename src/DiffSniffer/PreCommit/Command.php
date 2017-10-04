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
namespace DiffSniffer\PreCommit;

use DiffSniffer\Changeset as ChangesetInterface;
use DiffSniffer\Command as CommandInterface;

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
final class Command implements CommandInterface
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
    public function createChangeSet(array &$args) : ChangesetInterface
    {
        return new Changeset(new Cli(), getcwd());
    }
}
