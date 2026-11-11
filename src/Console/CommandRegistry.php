<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console;

use ArtisanShortcuts\Console\Commands\AliasListCommand;
use ArtisanShortcuts\Console\Commands\BinCommand;
use ArtisanShortcuts\Console\Commands\AliasRemoveCommand;
use ArtisanShortcuts\Console\Commands\AliasSetCommand;
use ArtisanShortcuts\Console\Commands\CommandsCommand;
use ArtisanShortcuts\Console\Commands\DoctorCommand;
use ArtisanShortcuts\Console\Commands\SailCommand;
use ArtisanShortcuts\Console\Commands\ShortcutsCommand;
use ArtisanShortcuts\Console\Commands\ShellCommand;
use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\ToolShortcuts;

final class CommandRegistry
{
    /** @var array<string, Command> */
    private array $handlers = [];

    public static function defaults(): self
    {
        $registry = new self();
        $registry->register('commands', new CommandsCommand());
        $registry->register('bin', new BinCommand());
        $registry->register('doctor', new DoctorCommand());
        $registry->register('shortcuts', new ShortcutsCommand());
        $registry->register('shell', new ShellCommand());
        $registry->register('alias:list', new AliasListCommand());
        $registry->register('alias:set', new AliasSetCommand());
        $registry->register('alias:remove', new AliasRemoveCommand());
        $registry->register('sail', new SailCommand());
        return $registry;
    }

    public function register(string $name, Command $command): void
    {
        if (!preg_match('/^[a-z][a-z0-9:_-]*$/D', $name) || isset($this->handlers[$name])) {
            throw new ShortcutException("Invalid or duplicate wrapper command: {$name}");
        }
        if (
            in_array($name, ['help', 'pa', 'pilot'], true)
            || isset(Resolver::DEFAULTS[$name])
            || isset(ToolShortcuts::DEFAULTS[$name])
        ) {
            throw new ShortcutException("Reserved wrapper command: {$name}");
        }
        $this->handlers[$name] = $command;
    }

    public function get(string $name): ?Command
    {
        return $this->handlers[$name] ?? null;
    }

    /** @return list<string> */
    public function names(): array
    {
        $names = array_keys($this->handlers);
        sort($names, SORT_STRING);
        return $names;
    }
}
