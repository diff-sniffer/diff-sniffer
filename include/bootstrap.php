<?php

declare(strict_types=1);

if (! file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo 'You must set up the project dependencies, run the following commands:'
        . PHP_EOL . 'curl -sS https://getcomposer.org/installer | php'
        . PHP_EOL . 'php composer.phar install'
        . PHP_EOL;
    exit(2);
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/squizlabs/php_codesniffer/autoload.php';
