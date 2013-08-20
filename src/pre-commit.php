<?php

/**
 * Pre-commit hook entry point
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
require __DIR__ . '/../vendor/autoload.php';

$arguments = $_SERVER['argv'];
array_shift($arguments);

$runner = new \DiffSniffer\Runner\Staged();
$return_var = $runner->run(getcwd(), $arguments);

exit($return_var);
