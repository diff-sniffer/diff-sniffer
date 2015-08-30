<?php

/**
 * Current version of DiffSniffer
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2015 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */
namespace DiffSniffer;

/**
 * Current version of DiffSniffer
 *
 * PHP version 5
 *
 * @category  DiffSniffer
 * @package   DiffSniffer
 * @author    Sergei Morozov <morozov@tut.by>
 * @copyright 2015 Sergei Morozov
 * @license   http://mit-license.org/ MIT Licence
 * @link      http://github.com/morozov/diff-sniffer-core
 */
class Version
{
    private static $version;

    /**
     * Returns the current version of DiffSniffer
     *
     * @return string
     */
    public static function getVersion()
    {
        if (self::$version === null) {
            $version = new \SebastianBergmann\Version('2.3', dirname(dirname(__DIR__)));
            self::$version = $version->getVersion();
        }

        return self::$version;
    }
}
