<?php

declare(strict_types=1);

namespace DiffSniffer;

use IteratorAggregate;
use IteratorIterator;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Filters\Filter;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Util\Common;
use RecursiveArrayIterator;
use Traversable;

use function iterator_to_array;
use function str_replace;

use const DIRECTORY_SEPARATOR;

/**
 * Changeset iterator
 *
 * @implements IteratorAggregate<int,File>
 */
final class Iterator implements IteratorAggregate
{
    /** @var Changeset */
    private $changeSet;

    /** @var Traversable<int,string> */
    private $absolutePaths;

    /** @var Config */
    private $config;

    /** @var Ruleset */
    private $ruleSet;

    /** @var string */
    private $cwd;

    /**
     * @param Traversable<int,string> $relativePaths
     */
    public function __construct(
        Traversable $relativePaths,
        Changeset $changeSet,
        Ruleset $ruleSet,
        Config $config,
        string $cwd
    ) {
        $this->absolutePaths = $this->absolutize($relativePaths, $cwd);
        $this->changeSet     = $changeSet;
        $this->ruleSet       = $ruleSet;
        $this->config        = $config;
        $this->cwd           = $cwd;
    }

    /**
     * @return Traversable<int,File>
     */
    public function getIterator(): Traversable
    {
        // PHP_CodeSniffer expects file paths to contain the native directory separator on Windows when matching them
        // against the exclude pattern but Git and GitHub REST API will return forward slashes regardless of the OS
        if (DIRECTORY_SEPARATOR === '\\') {
            $it = (function (): Traversable {
                foreach ($this->absolutePaths as $file) {
                    yield str_replace('/', DIRECTORY_SEPARATOR, $file);
                }
            })();
        } else {
            $it = new IteratorIterator($this->absolutePaths);
        }

        $it = new RecursiveArrayIterator(
            iterator_to_array($it)
        );

        $it = new Filter(
            $it,
            "\0",
            $this->config,
            $this->ruleSet
        );

        foreach ($it as $absolutePath) {
            yield $this->createFile(
                $absolutePath,
                $this->changeSet->getContents(
                    Common::stripBasepath($absolutePath, $this->cwd)
                )
            );
        }
    }

    /**
     * @param Traversable<int,string> $paths
     *
     * @return Traversable<int,string>
     */
    private function absolutize(Traversable $paths, string $cwd): Traversable
    {
        foreach ($paths as $path) {
            yield $cwd . DIRECTORY_SEPARATOR . $path;
        }
    }

    private function createFile(string $absolutePath, string $contents): File
    {
        $file       = new DummyFile($contents, $this->ruleSet, $this->config);
        $file->path = $absolutePath;

        return $file;
    }
}
