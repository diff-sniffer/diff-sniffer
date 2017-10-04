<?php
/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */
namespace DiffSniffer;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reporter as BaseReporter;

/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2017 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */
class Reporter extends BaseReporter
{
    /**
     * @var Diff
     */
    private $diff = [];

    public function __construct(Diff $diff, Config $config)
    {
        parent::__construct($config);

        $this->diff = $diff;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareFileReport(File $file) : array
    {
        return $this->diff->filter(
            $file->path,
            parent::prepareFileReport($file)
        );
    }
}
