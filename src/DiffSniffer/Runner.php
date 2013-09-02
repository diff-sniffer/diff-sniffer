<?php

/**
 * CodeSniffer runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
namespace DiffSniffer;

use DiffSniffer\CodeSniffer\Cli;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * CodeSniffer runner
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2013 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer
 */
class Runner
{
    /**
     * Runs CodeSniffer against specified changeset
     *
     * @param Changeset $changeset Changeset instance
     * @param array     $arguments PHP_CodeSniffer command line arguments
     *
     * @return int
     */
    public function run(Changeset $changeset, array $arguments = array())
    {
        $dir = $this->createTempDir();
        $this->scheduleDirectoryRemoval($dir);
        $changeset->export($dir);
        $diff = $changeset->getDiff();
        $diffPath = $this->storeDiff($diff);
        $this->scheduleFileRemoval($diffPath);
        $return_var = $this->runCodeSniffer($dir, $diffPath, $arguments);

        return $return_var;
    }

    /**
     * Create temporary directory
     *
     * @return string Directory path
     */
    protected function createTempDir()
    {
        $file = $this->createTempFile();
        unlink($file);
        mkdir($file);

        return $file;
    }

    /**
     * Schedule file removal
     *
     * @param string $path File path
     *
     * @return void
     */
    protected function scheduleFileRemoval($path)
    {
        register_shutdown_function(
            function () use ($path) {
                unlink($path);
            }
        );
    }

    /**
     * Schedule directory removal
     *
     * @param string $directory Directory path
     *
     * @return void
     */
    protected function scheduleDirectoryRemoval($directory)
    {
        register_shutdown_function(
            function () use ($directory) {
                $it = new RecursiveDirectoryIterator(
                    $directory,
                    RecursiveDirectoryIterator::SKIP_DOTS
                );
                $ri = new RecursiveIteratorIterator(
                    $it,
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                /** @var \SplFileInfo $file */
                foreach ($ri as $file) {
                    if ($file->isDir()) {
                        rmdir($file->getPathname());
                    } else {
                        unlink($file->getPathname());
                    }
                }

                rmdir($directory);
            }
        );
    }

    /**
     * Stores diff contents in a temporary file
     *
     * @param string $diff Diff contents
     *
     * @return string File path
     */
    protected function storeDiff($diff)
    {
        $file = $this->createTempFile();
        file_put_contents($file, $diff);

        return $file;
    }

    /**
     * Creates temporary file
     *
     * @return string File path
     */
    protected function createTempFile()
    {
        $dir = sys_get_temp_dir();
        $file = tempnam($dir, 'snf');

        return $file;
    }

    /**
     * Runs CodeSniffer
     *
     * @param string $dir      Base directory path
     * @param string $diffPath Diff file path
     * @param array  $options  Additional PHP_CodeSniffer options
     *
     * @return int
     */
    protected function runCodeSniffer($dir, $diffPath, array $options = array())
    {
        include_once __DIR__ . '/CodeSniffer/Reports/Xml.php';

        $_SERVER['PHPCS_DIFF_PATH'] = $diffPath;
        $_SERVER['PHPCS_BASE_DIR'] = $dir;

        $cli = new Cli();
        $cli->checkRequirements();

        $options = array_merge(
            $cli->getDefaults(),
            $options,
            array(
                'report_format' => 'xml',
                'files' => array($dir),
            )
        );

        $numErrors = $cli->process($options);

        return $numErrors === 0 ? 0 : 1;
    }
}
