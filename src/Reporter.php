<?php

declare(strict_types=1);

namespace DiffSniffer;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Reporter as BaseReporter;
use PHP_CodeSniffer\Util\Common;

/**
 * Wrapper report for PHP_CodeSniffer.
 */
class Reporter extends BaseReporter
{
    /** @var Diff */
    private $diff;

    /** @var string */
    private $cwd;

    public function __construct(Diff $diff, Config $config, string $cwd)
    {
        parent::__construct($config);

        $this->diff = $diff;
        $this->cwd  = $cwd;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public function prepareFileReport(File $file): array
    {
        return $this->diff->filter(
            Common::stripBasepath($file->getFilename(), $this->cwd),
            parent::prepareFileReport($file)
        );
    }
}
