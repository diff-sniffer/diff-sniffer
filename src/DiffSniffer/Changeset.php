<?php
/**
 * Abstract changeset interface
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
namespace DiffSniffer;

/**
 * Abstract changeset interface
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
interface Changeset
{
    /**
     * Returns diff of the changeset
     *
     * @return string
     */
    public function getDiff();

    /**
     * Exports the changed files into specified directory
     *
     * @param string $dir Target directory
     *
     * @return void
     */
    public function export($dir);
}
