<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShortcutException;

final class CommandsCommand implements Command
{
    public function usage(): string
    {
        return 'pa commands [NAMESPACE_OR_COMMAND] [--json]';
    }

    public function execute(Context $context, array $arguments): int
    {
        $filter = null;
        $json = false;
        foreach ($arguments as $argument) {
            if ($argument === '--json' && !$json) {
                $json = true;
            } elseif (!str_starts_with($argument, '-') && $argument !== '' && $filter === null) {
                $filter = rtrim($argument, ':');
            } else {
                throw new ShortcutException('Usage: ' . $this->usage());
            }
        }
        $commands = $context->commands();
        if ($filter !== null) {
            $commands = array_values(array_filter(
                $commands,
                static fn (string $name): bool => $name === $filter || str_starts_with($name, $filter . ':')
            ));
            if ($commands === []) {
                throw new ShortcutException("No installed Artisan commands match {$filter}.");
            }
        }
        if ($json) {
            fwrite(STDOUT, json_encode($commands, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
            return 0;
        }
        $groups = [];
        foreach ($commands as $command) {
            $namespace = str_contains($command, ':') ? explode(':', $command, 2)[0] : 'general';
            $groups[$namespace][] = $command;
        }
        ksort($groups, SORT_STRING);
        foreach ($groups as $namespace => $names) {
            fwrite(STDOUT, $namespace . "\n");
            foreach ($names as $name) {
                fwrite(STDOUT, '  ' . $name . "\n");
            }
        }
        return 0;
    }
}
