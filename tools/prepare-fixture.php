<?php

declare(strict_types=1);

$root = dirname(__DIR__) . '/tests/laravel';
foreach (['bootstrap/cache', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs'] as $directory) {
    if (!is_dir($root . '/' . $directory)) {
        mkdir($root . '/' . $directory, 0777, true);
    }
}
