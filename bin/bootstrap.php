<?php

declare(strict_types=1);

$autoload = null;
foreach (
    [
    $_composer_autoload_path ?? '',
    __DIR__ . '/../vendor/autoload.php',
    dirname(__DIR__, 3) . '/autoload.php',
    ] as $candidate
) {
    if ($candidate !== '' && is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}
if ($autoload === null) {
    fwrite(STDERR, "Artisan Pilot: dependencies missing. Run composer install.\n");
    exit(2);
}
require $autoload;
exit((new \ArtisanShortcuts\Application())->run(array_slice($argv, 1), basename($argv[0], '.bat')));
