<?php
/**
 * Abstract changeset interface
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

/**
 * Abstract changeset interface
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
interface Changeset
{
    /**
     * Returns diff of the changeset
     *
     * @return string
     * @throws Exception
     */
    public function getDiff() : string;

    /**
     * Returns contents of the given file
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function getContents(string $path) : string;
}
