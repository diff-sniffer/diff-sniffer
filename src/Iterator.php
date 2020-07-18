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
    private $files;

    /** @var Config */
    private $config;

    /** @var Ruleset */
    private $ruleSet;

    /**
     * @param Traversable<int,string> $files
     */
    public function __construct(Traversable $files, Changeset $changeSet, Ruleset $ruleSet, Config $config)
    {
        $this->files     = $files;
        $this->changeSet = $changeSet;
        $this->ruleSet   = $ruleSet;
        $this->config    = $config;
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
                foreach ($this->files as $file) {
                    yield str_replace('/', DIRECTORY_SEPARATOR, $file);
                }
            })();
        } else {
            $it = new IteratorIterator($this->files);
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

        foreach ($it as $path) {
            yield $this->createFile(
                $path,
                $this->changeSet->getContents($path)
            );
        }
    }

    private function createFile(string $path, string $contents): File
    {
        $file       = new DummyFile($contents, $this->ruleSet, $this->config);
        $file->path = $path;

        return $file;
    }
}
