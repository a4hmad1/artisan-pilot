<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class Project
{
    public static function find(string $start): string
    {
        $path = realpath($start);
        if ($path === false || !is_dir($path)) {
            throw new ShortcutException("Project directory does not exist: {$start}");
        }
        do {
            if (is_file($path . '/artisan')) {
                return $path;
            }
            $parent = dirname($path);
            if ($parent === $path) {
                break;
            }
            $path = $parent;
        } while (true);
        throw new ShortcutException('No artisan file found. Run inside a Laravel project or use --project PATH.');
    }
}
