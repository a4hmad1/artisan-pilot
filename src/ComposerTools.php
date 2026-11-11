<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use JsonException;

final class ComposerTools
{
    public static function validateName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/D', $name)) {
            throw new ShortcutException('Tool names must be Composer binary names, without paths or shell syntax.');
        }
    }

    public static function directory(string $project): string
    {
        $config = [];
        if (is_file($project . '/composer.json')) {
            try {
                $data = json_decode(
                    (string) file_get_contents($project . '/composer.json'),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException) {
                throw new ShortcutException('Invalid composer.json. Cannot locate project tools.');
            }
            if (!is_array($data) || (isset($data['config']) && !is_array($data['config']))) {
                throw new ShortcutException('Invalid Composer configuration.');
            }
            $config = $data['config'] ?? [];
        }
        $vendor = $config['vendor-dir'] ?? 'vendor';
        if (!is_string($vendor)) {
            throw new ShortcutException('Composer vendor-dir must be a string.');
        }
        $directory = getenv('COMPOSER_BIN_DIR') ?: ($config['bin-dir'] ?? $vendor . '/bin');
        if (!is_string($directory) || $directory === '' || str_contains($directory, "\0")) {
            throw new ShortcutException('Composer bin-dir must be a non-empty path.');
        }
        return str_replace('\\', '/', $directory);
    }

    public static function path(string $project, string $name): string
    {
        self::validateName($name);
        $directory = self::directory($project);
        $absolute = str_starts_with($directory, '/') || preg_match('~^[a-zA-Z]:/~', $directory);
        $path = ($absolute ? $directory : $project . '/' . $directory) . '/' . $name;
        if (!is_file($path)) {
            throw new ShortcutException(
                "Composer tool {$name} is not installed at {$path}. Install it in this project."
            );
        }
        $prefix = file_get_contents($path, false, null, 0, 1024);
        if ($prefix === false || !preg_match('/\A(?:\xEF\xBB\xBF)?(?:#![^\r\n]*\r?\n)?<\?php\b/', $prefix)) {
            throw new ShortcutException("Tool {$name} is not a PHP Composer binary. Run non-PHP tools directly.");
        }
        return $path;
    }
}
