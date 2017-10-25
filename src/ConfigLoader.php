<?php declare(strict_types=1);

namespace DiffSniffer;

use DiffSniffer\Exception\RuntimeException;

/**
 * Configuration loader
 */
class ConfigLoader
{
    /**
     * @param string $dir Current directory
     * @return array|null Configuration parameters or NULL if not found
     */
    public function loadConfig($dir) : ?array
    {
        $path = $this->findComposerConfig($dir);

        if ($path === null) {
            return null;
        }

        $composerConfig = $this->loadComposerConfig($path);
        $vendorDir = $composerConfig['vendor-dir'] ?? 'vendor';

        $path = sprintf('%s/%s/squizlabs/php_codesniffer/CodeSniffer.conf', dirname($path), $vendorDir);

        if (!file_exists($path)) {
            return null;
        }

        $config = $this->loadPhpCodeSnifferConfig($path);

        return $this->massageConfig($config, $path);
    }

    /**
     * Finds path of composer.json located in the closest common parent directory
     *
     * @param string $dir Current directory
     * @return string|null
     */
    private function findComposerConfig(string $dir) : ?string
    {
        do {
            $path = $dir . '/composer.json';

            if (file_exists($path)) {
                return $path;
            }

            $prev = $dir;
            $dir = dirname($dir);
        } while ($dir !== $prev);

        return null;
    }

    /**
     * Loads composer configuration from the given file
     *
     * @param string $path File path
     * @return array Configuration parameters
     */
    private function loadComposerConfig(string $path) : array
    {
        $contents = file_get_contents($path);
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
    private function loadPhpCodeSnifferConfig(string $path) : array
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
