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
 * @link      http://github.com/morozov/diff-sniffer-core
 */
namespace DiffSniffer;

/**
 * @param array  $argv   The value of $_SERVER['argv']
 * @param string $config Path to config file
 *
 * @return array         PHP_CodeSniffer arguments
 */
function getCodeSnifferArguments(array $argv, $config)
{
    $arguments = $argv;
    array_shift($arguments);

    if (file_exists($config)) {
        $arguments = array_merge($arguments, include $config);
    }

    return $arguments;
}
