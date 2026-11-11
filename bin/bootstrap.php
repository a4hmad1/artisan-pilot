<?php

declare(strict_types=1);

$autoload = $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Artisan Pilot: dependencies missing. Run composer install.\n");
    exit(2);
}
require $autoload;
exit((new \ArtisanShortcuts\Application())->run(array_slice($argv, 1), basename($argv[0], '.bat')));
