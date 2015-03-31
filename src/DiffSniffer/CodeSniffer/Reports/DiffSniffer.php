<?php
/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2014 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */

/**
 * Wrapper report for PHP_CodeSniffer.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2014 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */
class PHP_CodeSniffer_Reports_DiffSniffer implements PHP_CodeSniffer_Report
{
    /**
     * Temporary diff file path.
     *
     * @var string
     */
    protected $diffPath;

    /**
     * The directory where the files to be checked reside.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * Requested report type
     *
     * @var string
     */
    protected $reportType;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $diffPath = $this->getPath('PHPCS_DIFF_PATH');
        if (!is_file($diffPath)) {
            throw new PHP_CodeSniffer_Exception(
                $diffPath . ' is not a file'
            );
        }
        $this->diffPath = $diffPath;

        $baseDir = $this->getPath('PHPCS_BASE_DIR');
        if (!is_dir($baseDir)) {
            throw new PHP_CodeSniffer_Exception(
                $diffPath . ' is not a directory'
            );
        }
        $this->baseDir = $baseDir;

        $this->reportType = $this->getEnv('PHPCS_REPORT_TYPE');
    }

    /**
     * Retrieves the value of environment variable.
     *
     * @param string $varName Environment variable name
     *
     * @return string
     * @throws PHP_CodeSniffer_Exception
     */
    protected function getEnv($varName)
    {
        if (!isset($_SERVER[$varName])) {
            throw new PHP_CodeSniffer_Exception(
                $varName . ' environment variable is not set'
            );
        }

        return $_SERVER[$varName];
    }

    /**
     * Retrieves the path specified by environment variable.
     *
     * @param string $varName Environment variable name
     *
     * @return string
     * @throws PHP_CodeSniffer_Exception
     */
    protected function getPath($varName)
    {
        $value = $this->getEnv($varName);
        $path = realpath($value);

        if (false === $path) {
            throw new PHP_CodeSniffer_Exception(
                $value . ' path does not exist'
            );
        }

        return $path;
    }

    /** {@inheritDoc} */
    public function generateFileReport(
        $report,
        PHP_CodeSniffer_File $phpcsFile,
        $showSources = false,
        $width = 80
    ) {
        $diff = $this->getStagedDiff();
        $changes = $this->getChanges($diff);

        $report = $this->filterReport($report, $changes);

        $reporting = new PHP_CodeSniffer_Reporting();
        $actual = $reporting->factory($this->reportType);
        return $actual->generateFileReport($report, $phpcsFile, $showSources, $width);
    }

    /** {@inheritDoc} */
    public function generate(
        $cachedData,
        $totalFiles,
        $totalErrors,
        $totalWarnings,
        $totalFixable,
        $showSources = false,
        $width = 80,
        $toScreen = true
    ) {
        echo $cachedData;

        if ($toScreen === true
            && PHP_CODESNIFFER_INTERACTIVE === false
            && class_exists('PHP_Timer', false) === true
        ) {
            echo PHP_Timer::resourceUsage().PHP_EOL.PHP_EOL;
        }
    }

    /**
     * Returns diff contents
     *
     * @return string
     * @throws PHP_CodeSniffer_Exception
     */
    protected function getStagedDiff()
    {
        $contents = file_get_contents($this->diffPath);

        return $contents;
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

    /**
     * Filters report producing another one containing only changed lines
     *
     * @param array $report  Original report
     * @param array $changes Staged changes
     *
     * @return array
     */
    protected function filterReport(array $report, array $changes)
    {
        $filename = $report['filename'];
        if (strpos($filename, $this->baseDir . DIRECTORY_SEPARATOR) === 0) {
            $relative = substr($filename, strlen($this->baseDir) + 1);
            $report['filename'] = $relative;
            if (isset($changes[$relative])) {
                $report['messages'] = array_intersect_key(
                    $report['messages'],
                    array_flip($changes[$relative])
                );
            } else {
                $report['messages'] = array();
            }
        }

        return $this->repairReport($report);
    }

    /**
     * Generates report from file data
     *
     * @param array $report File data
     *
     * @return array
     */
    protected function repairReport(array $report)
    {
        $repaired = array_merge($report, array(
            'errors'   => 0,
            'warnings' => 0,
            'fixable' => 0,
        ));

        foreach ($report['messages'] as $columns) {
            foreach ($columns as $messages) {
                foreach ($messages as $message) {
                    switch($message['type']) {
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
                    $repaired[$key]++;

                    if ($message['fixable']) {
                        $repaired['fixable']++;
                    }
                }
            }
        }

        return $repaired;
    }
}
