<?php

/**
 * CodeSniffer CLI wrapper
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
namespace DiffSniffer\CodeSniffer;

/**
 * CodeSniffer CLI wrapper
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
class Cli extends \PHP_CodeSniffer_CLI
{
    /**
     * Runs PHP_CodeSniffer over files and directories.
     *
     * @param array $values An array of values determined from CLI args.
     *
     * @return int The number of error and warning messages shown.
     */
    public function process($values = array())
    {
        $this->values = $values;
        return parent::process($values);
    }
}
