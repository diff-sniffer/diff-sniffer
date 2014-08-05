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
$autoload = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoload)) {
    echo 'You must set up the project dependencies, run the following commands:'
        . PHP_EOL . 'curl -sS https://getcomposer.org/installer | php'
        . PHP_EOL . 'php composer.phar install'
        . PHP_EOL;
    exit(2);
}

require $autoload;

$arguments = DiffSniffer\getCodeSnifferArguments(
    $_SERVER['argv'],
    __DIR__ . '/../config.php'
);

if ($arguments && $arguments[0] == '--version') {
    echo 'Diff Sniffer Pre-Commit Hook version 1.0.0' . PHP_EOL;
    $cli = new PHP_CodeSniffer_CLI();
    $cli->processLongArgument('version', null, null);
    exit;
}

$runner = new \DiffSniffer\Runner\Staged();
$return_var = $runner->run(getcwd(), $arguments);

exit($return_var);
