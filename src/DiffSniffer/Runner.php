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

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

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
     * Path to CodeSniffer executable
     *
     * @var string
     */
    protected $phpCsBin;

    /**
     * Additional include_path for PEAR class loading
     *
     * @var string
     */
    protected $includePath;

    /**
     * Constructor
     *
     * @global string $phpBin      Path to PHP interpreter
     * @global string $phpCsBin    Path to CodeSniffer executable
     * @global string $includePath Additional include_path for PEAR class loading
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        global $DIFF_SNIFFER_CORE_ROOT;
        global $DIFF_SNIFFER_APP_ROOT;

        $phpCsBin = $DIFF_SNIFFER_APP_ROOT . '/vendor/bin/phpcs';
        if (!is_file($phpCsBin)) {
            throw new \RuntimeException('Path to CodeSniffer is not a file');
        }

        $includePath = $DIFF_SNIFFER_CORE_ROOT . '/src';
        if (!is_dir($includePath)) {
            throw new \RuntimeException('include_path is not a directory');
        }

        $this->phpCsBin = $phpCsBin;
        $this->includePath = $includePath;
    }

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
     * @param string $dir       Base directory path
     * @param string $diffPath  Diff file path
     * @param array  $arguments PHP_CodeSniffer command line arguments
     *
     * @return int
     */
    protected function runCodeSniffer($dir, $diffPath, array $arguments)
    {
        $autoPrependFile = $this->includePath . '/PHP/CodeSniffer/Reports/Xml.php';

        $cmd = $this->getCommand($autoPrependFile, $dir, $arguments);

        $process = new Process(
            $cmd,
            null,
            array(
                'PHPCS_DIFF_PATH' => $diffPath,
                'PHPCS_BASE_DIR' => $dir,
            ),
            null
        );

        $return_value = $process->run(
            function ($type, $buffer) {
                switch ($type) {
                    case Process::OUT:
                        fwrite(STDOUT, $buffer);
                        break;
                    case Process::ERR:
                        fwrite(STDERR, $buffer);
                        break;
                }
            }
        );

        return $return_value;
    }

    /**
     * Prepare command for running PHP_CodeShiffer
     *
     * @param string $autoPrependFile Path to prepended file
     * @param string $dir             Directory to be scanned
     * @param array  $arguments       PHP_CodeSniffer command line arguments
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getCommand($autoPrependFile, $dir, $arguments)
    {
        $finder = new PhpExecutableFinder();
        if (false === $php = $finder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable');
        }

        $arguments = array_filter(
            $arguments,
            function ($argument) {
                return strpos($argument, '--report') === false;
            }
        );

        $arguments = array_merge(
            array(
                $php,
                '-d',
                'include_path=' . ini_get('include_path')
                    . PATH_SEPARATOR . $this->includePath,
                '-d',
                'auto_prepend_file=' . $autoPrependFile,
            ),
            array(
                $this->phpCsBin,
            ),
            $arguments,
            array(
                '--report=xml',
                $dir
            )
        );

        $arguments = array_map('escapeshellarg', $arguments);

        $cmd = implode(' ', $arguments);

        return $cmd;
    }
}
