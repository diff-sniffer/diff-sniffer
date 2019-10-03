<?php declare(strict_types=1);

namespace DiffSniffer;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Diff
 */
class Diff implements IteratorAggregate, Countable
{
    /**
     * @var array<string,int[]>
     */
    private $paths;

    /**
     * Constructor
     *
     * @param string $diff Textual representation of the diff
     */
    public function __construct(string $diff)
    {
        $this->paths = $this->parse($diff);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator(
            array_keys($this->paths)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function count() : int
    {
        return count($this->paths);
    }

    /**
     * Filters file report producing another one containing only the lines affected by diff
     *
     * @param string $path File path
     * @param array $report Report data
     *
     * @return array
     */
    public function filter(string $path, array $report) : array
    {
        $report['messages'] = isset($this->paths[$path]) ? array_intersect_key(
            $report['messages'],
            array_flip($this->paths[$path])
        ) : [];

        $errors = $warnings = $fixable = 0;

        foreach ($report['messages'] as $line) {
            foreach ($line as $messages) {
                foreach ($messages as $message) {
                    switch ($message['type']) {
                        case 'ERROR':
                            $errors++;
                            break;
                        case 'WARNING':
                            $warnings++;
                            break;
                    }

                    if ($message['fixable']) {
                        $fixable++;
                    }
                }
            }
        }

        return array_merge($report, [
            'errors' => $errors,
            'warnings' => $warnings,
            'fixable' => $fixable,
        ]);
    }

    /**
     * Parses diff and returns array containing affected paths and line numbers
     *
     * @param string $diff Diff output
     *
     * @return array
     */
    private function parse(string $diff) : array
    {
        $diff = preg_split("/((\r?\n)|(\r\n?))/", $diff);
        assert(is_array($diff));

        $paths = [];
        $number = 0;
        $path = null;

        foreach ($diff as $line) {
            if (preg_match('~^\+\+\+\s(.*)~', $line, $matches)) {
                $path = substr($matches[1], strpos($matches[1], '/') + 1);
            } elseif (preg_match(
                '~^@@ -[0-9]+,[0-9]+? \+([0-9]+),[0-9]+? @@.*$~',
                $line,
                $matches
            )) {
                $number = (int) $matches[1];
            } elseif (preg_match('~^\+(.*)~', $line, $matches)) {
                $paths[$path][] = $number;
                $number++;
            } elseif (preg_match('~^[^-]+(.*)~', $line, $matches)) {
                $number++;
            }
        }

        return $paths;
    }
}
