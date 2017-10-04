<?php

/**
 * Exception indicating bad command usage
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer\Exception
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
namespace DiffSniffer\Command\Exception;

use BadMethodCallException;
use DiffSniffer\Exception;

/**
 * Exception indicating bad command usage
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer\Exception
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
class BadUsage extends BadMethodCallException implements Exception
{
}
