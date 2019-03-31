<?php declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Exception\RuntimeException;

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
     * @return array|null Configuration parameters or NULL if not found
     */
    public function getPhpCodeSnifferConfiguration() : ?array
    {
        if ($this->root === null) {
            return null;
        }

        $path = sprintf('%s/%s/squizlabs/php_codesniffer/CodeSniffer.conf', $this->root, $this->getVendorDirectory());

        if (!file_exists($path)) {
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

        if (!is_file($path)) {
            return;
        }

        require $path;
    }

    /**
     * Finds project root by locating composer.json located in the current working directory or its closest parent
     *
     * @param string $dir Current working directory
     * @return string|null
     */
    private function findProjectRoot(string $dir) : ?string
    {
        do {
            $path = $dir . '/composer.json';

            if (file_exists($path)) {
                return $dir;
            }

            $prev = $dir;
            $dir = dirname($dir);
        } while ($dir !== $prev);

        return null;
    }

    private function getVendorDirectory() : string
    {
        if (!$this->composerConfigurationLoaded) {
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
     * @return array Configuration parameters
     */
    private function loadComposerConfiguration() : array
    {
        $contents = file_get_contents($this->root . '/composer.json');
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
     * @return array Configuration parameters
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
     * @param array $config Configuration parameters
     * @param string $configPath Configuration file paths
     * @return array Configuration parameters
     */
    private function massageConfig(array $config, string $configPath) : array
    {
        if (!isset($config['installed_paths'])) {
            return $config;
        }

        $config['installed_paths'] = implode(',', array_map(function ($path) use ($configPath) {
            return dirname($configPath) . '/' . $path;
        }, explode(',', $config['installed_paths'])));

        return $config;
    }
}
