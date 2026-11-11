<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use JsonException;

final class AliasStore
{
    /** @param list<string> $reservedNames */
    public function __construct(
        private readonly string $project,
        private readonly array $reservedNames = Resolver::INTERNAL
    ) {
    }

    /** @return array<string, non-empty-list<string>> */
    public function all(): array
    {
        return $this->read();
    }

    /** @param list<string> $target
     *  @param list<string> $commands
     */
    public function set(string $name, array $target, array $commands): void
    {
        if (!preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $name)) {
            throw new ShortcutException(
                'Alias names start with a lowercase letter and contain a-z, 0-9, _ or - (64 characters maximum).'
            );
        }
        if (
            isset(Resolver::DEFAULTS[$name])
            || in_array($name, $this->reservedNames, true)
            || in_array($name, $commands, true)
            || in_array($name, ['pa', 'pilot'], true)
        ) {
            throw new ShortcutException("Alias {$name} is reserved by a shortcut, command, or management action.");
        }
        if ($target === [] || !in_array($target[0], $commands, true)) {
            throw new ShortcutException('Alias target must be an exact Artisan command from pa -- list.');
        }
        foreach ($target as $argument) {
            if (str_contains($argument, "\0")) {
                throw new ShortcutException('Alias arguments must not contain NUL bytes.');
            }
        }
        $this->update(static function (array $aliases) use ($name, $target): array {
            $aliases[$name] = $target;
            return $aliases;
        });
    }

    public function remove(string $name): void
    {
        $this->update(static function (array $aliases) use ($name): array {
            if (!isset($aliases[$name])) {
                throw new ShortcutException("Alias {$name} does not exist.");
            }
            unset($aliases[$name]);
            return $aliases;
        });
    }

    /** @return array<string, non-empty-list<string>> */
    private function read(): array
    {
        $path = $this->project . '/.artisan-shortcuts.json';
        if (!file_exists($path)) {
            return [];
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new ShortcutException("Cannot read {$path}.");
        }
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ShortcutException("Invalid JSON in {$path}. Fix the file before continuing.");
        }
        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new ShortcutException('Alias file must contain a JSON object mapping names to argument arrays.');
        }
        $validated = [];
        foreach ($data as $name => $target) {
            if (
                !is_string($name) || !preg_match('/^[a-z][a-z0-9_-]{0,63}$/D', $name)
                || !is_array($target) || !array_is_list($target) || $target === []
            ) {
                throw new ShortcutException('Invalid alias entry. Expected a name and a non-empty array of strings.');
            }
            foreach ($target as $value) {
                if (!is_string($value) || str_contains($value, "\0")) {
                    throw new ShortcutException('Alias arguments must be strings without NUL bytes.');
                }
            }
            if ($target[0] === '' || str_starts_with($target[0], '-')) {
                throw new ShortcutException('Alias target must start with an Artisan command.');
            }
            $validated[$name] = $target;
        }
        ksort($validated, SORT_STRING);
        return $validated;
    }

    /** @param callable(array<string, non-empty-list<string>>): array<string, non-empty-list<string>> $change */
    private function update(callable $change): void
    {
        $path = $this->project . '/.artisan-shortcuts.json';
        $lock = fopen($path . '.lock', 'c');
        if ($lock === false) {
            throw new ShortcutException('Cannot open alias lock. Check project permissions.');
        }
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new ShortcutException('Cannot lock alias file.');
            }
            $aliases = $change($this->read());
            ksort($aliases, SORT_STRING);
            $json = json_encode(
                (object) $aliases,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ) . "\n";
            $temporary = tempnam($this->project, '.shortcuts-');
            if ($temporary === false) {
                throw new ShortcutException('Cannot create temporary alias file.');
            }
            try {
                if (file_put_contents($temporary, $json) !== strlen($json) || !rename($temporary, $path)) {
                    throw new ShortcutException('Cannot save alias file. Check project permissions.');
                }
            } finally {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
