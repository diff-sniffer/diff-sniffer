<?php declare(strict_types=1);

namespace DiffSniffer;

use FilterIterator;
use PHP_CodeSniffer\Filters\Filter as BaseFilter;
use ReflectionMethod;

/**
 * File filter
 */
final class Filter extends FilterIterator
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
