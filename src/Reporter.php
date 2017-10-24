<?php

namespace DiffSniffer;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reporter as BaseReporter;

/**
 * Wrapper report for PHP_CodeSniffer.
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
