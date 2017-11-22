<?php declare(strict_types=1);

namespace DiffSniffer;

/**
 * Application version
 */
final class VersionFormatter
{
    /**
     * Formats version into a human-readable representation
     *
     * @param string $fullVersion
     * @return string
     */
    public function format(string $fullVersion)
    {
        list($version, $hash) = explode('@', $fullVersion);

        $version = preg_replace(
            '/(\.9{7})+-dev$/',
            '-dev@' . preg_quote(substr($hash, 0, 7)),
            $version,
            -1,
            $count
        );

        if ($count) {
            return $version;
        }

        $numbers = explode('.', $version);

        while (count($numbers) > 3 && end($numbers) === '0') {
            array_pop($numbers);
        }

        return implode('.', $numbers);
    }
}
