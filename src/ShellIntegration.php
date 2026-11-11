<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class ShellIntegration
{
    public const SHELLS = ['bash', 'zsh', 'fish', 'powershell'];

    /** @param list<string> $commands
     *  @param array<string, non-empty-list<string>> $aliases
     *  @param list<string> $reserved
     *  @return list<string>
     */
    public function names(array $commands, array $aliases, array $reserved): array
    {
        $resolver = new Resolver();
        $names = array_values(array_unique([
            ...array_keys(Resolver::DEFAULTS),
            ...array_keys(ToolShortcuts::DEFAULTS),
            ...array_keys($resolver->abbreviations($commands)),
            ...array_keys($aliases),
        ]));
        $available = [];
        foreach ($names as $name) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/D', $name) || in_array($name, ['pa', 'pilot', ...$reserved], true)) {
                continue;
            }
            if (!isset(Resolver::DEFAULTS[$name]) && !isset(ToolShortcuts::DEFAULTS[$name])) {
                try {
                    $resolver->resolve($name, $commands, $aliases);
                } catch (ShortcutException) {
                    continue;
                }
            }
            $available[] = $name;
        }
        sort($available, SORT_STRING);
        return $available;
    }

    /** @param list<string> $names */
    public function render(string $shell, array $names, string $entrypoint, string $php): string
    {
        if (!in_array($shell, self::SHELLS, true)) {
            throw new ShortcutException('Shell must be bash, zsh, fish, or powershell.');
        }
        $script = '';
        foreach ($names as $name) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/D', $name)) {
                throw new ShortcutException('Unsafe shell shortcut name.');
            }
            $arguments = [$php, $entrypoint, '--shortcut=' . $name];
            if ($shell === 'powershell') {
                $command = implode(' ', array_map($this->powershellQuote(...), $arguments));
                $script .= "function global:{$name} { & {$command} @args }\n";
            } elseif ($shell === 'fish') {
                $command = implode(' ', array_map($this->fishQuote(...), $arguments));
                $script .= "function {$name}\n    command {$command} \$argv\nend\n";
            } else {
                $command = implode(' ', array_map($this->posixQuote(...), $arguments));
                $script .= "{$name}() { command {$command} \"\$@\"; }\n";
            }
        }
        return $script;
    }

    private function posixQuote(string $value): string
    {
        return "'" . str_replace("'", "'\\''", $value) . "'";
    }

    private function powershellQuote(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function fishQuote(string $value): string
    {
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
    }
}
