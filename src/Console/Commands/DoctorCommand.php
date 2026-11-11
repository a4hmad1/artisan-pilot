<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Version;

final class DoctorCommand implements Command
{
    public function usage(): string
    {
        return 'pilot doctor';
    }

    public function execute(Context $context, array $arguments): int
    {
        $context->expectCount($arguments, 0, $this->usage());
        $commands = $context->commands();
        $aliases = $context->aliases->all();
        foreach ($aliases as $name => $target) {
            if (!in_array($target[0], $commands, true)) {
                throw new ShortcutException("Alias {$name} targets missing command {$target[0]}.");
            }
        }
        fwrite(
            STDOUT,
            'Artisan Pilot ' . Version::CURRENT . "\nProject: {$context->project}\nPHP: " . PHP_VERSION
            . "\nPHP binary: {$context->options->php}\nRunner: {$context->options->runner}"
            . "\nCommands: " . count($commands) . "\nAliases: " . count($aliases)
            . "\nAlias file: valid\nDiscovery: OK\n"
        );
        return 0;
    }
}
