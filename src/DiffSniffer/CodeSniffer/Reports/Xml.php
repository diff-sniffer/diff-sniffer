<?php
/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */

/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
class PHP_CodeSniffer_Reports_Xml implements PHP_CodeSniffer_Report
{
    /**
     * Prints errors and warnings found only in lines modified in commit.
     *
     * Errors and warnings are displayed together, grouped by file.
     *
     * @param array   $report      Prepared report.
     * @param boolean $showSources Show sources?
     * @param int     $width       Maximum allowed lne width.
     * @param boolean $toScreen    Is the report being printed to screen?
     *
     * @return string
     */
    public function generate(
        $report,
        $showSources = false,
        $width = 80,
        $toScreen = true
    ) {
        if (!isset($_SERVER['PHPCS_DIFF_PATH'])) {
            throw new PHP_CodeSniffer_Exception(
                'PHPCS_DIFF_PATH environment variable is not set'
            );
        }
        $diffPath = $_SERVER['PHPCS_DIFF_PATH'];
        $diff = file_get_contents($diffPath);

        if (!isset($_SERVER['PHPCS_DIFF_PATH'])) {
            throw new PHP_CodeSniffer_Exception(
                'PHPCS_DIFF_PATH environment variable is not set'
            );
        }
        $baseDir = $_SERVER['PHPCS_BASE_DIR'];

        $report = $this->filter($report, $baseDir, $diff);

        $full = new PHP_CodeSniffer_Reports_Full();
        return $full->generate($report, $showSources, $width, $toScreen);
    }

    /**
     * Filters report producing another one containing only changed lines
     *
     * @param array  $report  Original report
     * @param string $baseDir The directory where the files are located
     * @param string $diff    Diff that should be used for filtering
     *
     * @return array
     */
    protected function filter(array $report, $baseDir, $diff)
    {
        $changes = $this->getChanges($diff);

        $files = array();
        foreach ($changes as $relPath => $lines) {
            $absPath = $baseDir . DIRECTORY_SEPARATOR
                . str_replace('/', DIRECTORY_SEPARATOR, $relPath);

            if (isset($report['files'][$absPath])) {
                $files[$relPath]['messages'] = array_intersect_key(
                    $report['files'][$absPath]['messages'],
                    array_flip($lines)
                );
            }
        }

        return $this->getReport($files);
    }

    /**
     * Generates report from file data
     *
     * @param array $files File data
     *
     * @return array
     */
    protected function getReport(array $files)
    {
        $totals = array(
            'warnings' => 0,
            'errors'   => 0,
        );

        foreach ($files as $path => $file) {
            $files[$path] = array_merge($file, $totals);
            foreach ($file['messages'] as $columns) {
                foreach ($columns as $messages) {
                    foreach ($messages as $message) {
                        switch ($message['type']) {
                            case 'ERROR':
                                $key = 'errors';
                                break;
                            case 'WARNING':
                                $key = 'warnings';
                                break;
                            default:
                                $key = null;
                                continue;
                        }
                        $files[$path][$key]++;
                        $totals[$key]++;
                    }
                }
            }
        }

        return array(
            'totals' => $totals,
            'files'  => $files,
        );
    }

    /**
     * Parses diff and returns array containing affected paths and line numbers
     *
     * @param string $lines Diff output
     *
     * @return array
     */
    protected function getChanges($lines)
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $lines);
        $changes = array();
        $number = 0;
        $path = null;
        foreach ($lines as $line) {
            if (preg_match('~^\+\+\+\s(.*)~', $line, $matches)) {
                $path = substr($matches[1], strpos($matches[1], '/') + 1);
            } elseif (preg_match(
                '~^@@ -[0-9]+,[0-9]+? \+([0-9]+),[0-9]+? @@.*$~',
                $line,
                $matches
            )) {
                $number = (int) $matches[1];
            } elseif (preg_match('~^\+(.*)~', $line, $matches)) {
                $changes[$path][] = $number;
                $number++;
            } elseif (preg_match('~^[^-]+(.*)~', $line, $matches)) {
                $number++;
            }
        }
        return $changes;
    }
}
