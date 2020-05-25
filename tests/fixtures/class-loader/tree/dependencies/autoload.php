<?php

spl_autoload_register(function (string $class) : void {
    if ($class === Dummy::class) {
        require __DIR__ . '/../Dummy.php';
    }
}, true, true);
