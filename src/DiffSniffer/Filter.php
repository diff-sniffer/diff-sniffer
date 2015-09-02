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

use FilterIterator;
use PHP_CodeSniffer\Filters\Filter as BaseFilter;
use ReflectionMethod;

/**
 * File filter
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
class Filter extends FilterIterator
{
    /**
     * @var \Closure
     */
    private $callback;

    public function __construct(\Iterator $it, BaseFilter $filter)
    {
        parent::__construct($it);

        $re = new ReflectionMethod($filter, 'shouldProcessFile');
        $re->setAccessible(true);

        $this->callback = function () use ($it, $filter, $re) {
            return $re->invoke($filter, $it->current());
        };
    }

    public function accept() : bool
    {
        return ($this->callback)();
    }
}
