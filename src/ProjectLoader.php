<?php

declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Exception\RuntimeException;
use PHP_CodeSniffer\Autoload;
use function array_map;
use function assert;
use function dirname;
use function error_get_last;
use function explode;
use function file_exists;
use function file_get_contents;
use function implode;
use function is_array;
use function is_file;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function sprintf;
use function substr;
use const JSON_ERROR_NONE;

/**
 * Configuration loader
 */
class ProjectLoader
{
    /**
     * Project root
     *
     * @var string|null
     */
    private $root;

    /**
     * Indicates whether Composer configuration was loaded
     *
     * @var bool
     */
    private $composerConfigurationLoaded = false;

    /**
     * Composer vendor directory
     *
     * @var string
     */
    private $vendorDirectory = 'vendor';

    /**
     * @param string $directory Current working directory
     */
    public function __construct(string $directory)
    {
        $this->root = $this->findProjectRoot($directory);
    }

    /**
     * @return array<string,mixed>|null Configuration parameters or NULL if not found
     */
    public function getPhpCodeSnifferConfiguration() : ?array
    {
        if ($this->root === null) {
            return null;
        }

        $path = sprintf('%s/%s/squizlabs/php_codesniffer/CodeSniffer.conf', $this->root, $this->getVendorDirectory());

        if (! file_exists($path)) {
            return null;
        }

        $config = $this->loadPhpCodeSnifferConfiguration($path);

        return $this->massageConfig($config, $path);
    }

    /**
     * Registers class loader if it's defined in the project
     */
    public function registerClassLoader() : void
    {
        if ($this->root === null) {
            return;
        }

        $path = sprintf('%s/%s/autoload.php', $this->root, $this->getVendorDirectory());

        if (! is_file($path)) {
            return;
        }

        require $path;

        // Re-prepend PHP_CodeSniffer's autoloader in order to let it stay on top of the stack
        // and track the loading of its own classes. Otherwise, if a sniff is loaded by another
        // loader, it may be loaded again by the PHP_CodeSniffer's loader
        spl_autoload_unregister([Autoload::class, 'load']);
        spl_autoload_register([Autoload::class, 'load'], true, true);
    }

    /**
     * Finds project root by locating composer.json located in the current working directory or its closest parent
     *
     * @param string $dir Current working directory
     */
    private function findProjectRoot(string $dir) : ?string
    {
        do {
            $path = $dir . '/composer.json';

            if (file_exists($path)) {
                return $dir;
            }

            // this is only possible when testing within a virtual filesystem
            // but we need to handle it here in order to let tests pass
            if (substr($dir, -1) === ':') {
                break;
            }

            $prev = $dir;
            $dir  = dirname($dir);
        } while ($dir !== $prev);

        return null;
    }

    private function getVendorDirectory() : string
    {
        if (! $this->composerConfigurationLoaded) {
            $config = $this->loadComposerConfiguration();

            if (isset($config['vendor-dir'])) {
                $this->vendorDirectory = $config['vendor-dir'];
            }

            $this->composerConfigurationLoaded = true;
        }

        return $this->vendorDirectory;
    }

    /**
     * Loads composer configuration from the given file
     *
     * @return array<string,mixed> Configuration parameters
     */
    private function loadComposerConfiguration() : array
    {
        $contents = file_get_contents($this->root . '/composer.json');

        if ($contents === false) {
            $error = error_get_last();
            assert(is_array($error));

            throw new RuntimeException($error['message']);
        }

        $config = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }

        return $config;
    }

    /**
     * Loads PHP_CodeSniffer configuration from the given file
     *
     * @param string $path File path
     *
     * @return array<string,mixed> Configuration parameters
     */
    private function loadPhpCodeSnifferConfiguration(string $path) : array
    {
        $phpCodeSnifferConfig = [];

        require $path;

        return $phpCodeSnifferConfig;
    }

    /**
     * Resolves relative installed paths against the configuration file path
     *
     * @param array<string,mixed> $config     Configuration parameters
     * @param string              $configPath Configuration file paths
     *
     * @return array<string,mixed> Configuration parameters
     */
    private function massageConfig(array $config, string $configPath) : array
    {
        if (! isset($config['installed_paths'])) {
            return $config;
        }

        $config['installed_paths'] = implode(',', array_map(static function ($path) use ($configPath) {
            return dirname($configPath) . '/' . $path;
        }, explode(',', $config['installed_paths'])));

        return $config;
    }
}
