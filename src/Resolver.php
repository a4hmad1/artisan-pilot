<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class Resolver
{
    public const DEFAULTS = [
        'pas' => 'serve', 'pat' => 'tinker', 'pad' => 'down', 'paup' => 'up',
        'pam' => 'migrate', 'pamf' => 'migrate:fresh', 'pamm' => 'make:model',
        'pamc' => 'make:controller', 'parl' => 'route:list', 'paoc' => 'optimize:clear',
    ];
    public const INTERNAL = [
        'help', 'commands', 'shortcuts', 'doctor', 'alias:set', 'alias:remove', 'alias:list', 'sail',
    ];

    /** @param list<string> $commands
     *  @return array<string, list<string>>
     */
    public function abbreviations(array $commands): array
    {
        $index = [];
        sort($commands, SORT_STRING);
        foreach (array_unique($commands) as $command) {
            $parts = preg_split('/[:_\-]+/', $command, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $short = 'pa';
            foreach ($parts as $part) {
                $short .= $part[0];
            }
            $index[$short][] = $command;
        }
        ksort($index, SORT_STRING);
        return $index;
    }

    /** @param list<string> $commands
     *  @param array<string, non-empty-list<string>> $aliases
     *  @return non-empty-list<string>
     */
    public function resolve(string $name, array $commands, array $aliases): array
    {
        // Fixed shortcuts and exact command names never change meaning as commands are added.
        if (isset(self::DEFAULTS[$name])) {
            $target = self::DEFAULTS[$name];
            if (!in_array($target, $commands, true)) {
                throw new ShortcutException("Shortcut {$name} needs unavailable command {$target}.");
            }
            return [$target];
        }
        if (in_array($name, $commands, true)) {
            return [$name];
        }
        if (isset($aliases[$name])) {
            $target = $aliases[$name];
            if (!in_array($target[0], $commands, true)) {
                throw new ShortcutException("Alias {$name} targets missing command {$target[0]}. Update or remove it.");
            }
            return $target;
        }
        $matches = $this->abbreviations($commands)[$name] ?? [];
        if (count($matches) === 1) {
            return [$matches[0]];
        }
        if (count($matches) > 1) {
            throw new ShortcutException(
                "Ambiguous shortcut {$name}: " . implode(', ', $matches)
                . '. Use an exact command or a custom alias.'
            );
        }
        throw new ShortcutException("Unknown command or shortcut {$name}. Run pilot commands or pilot shortcuts.");
    }
}
