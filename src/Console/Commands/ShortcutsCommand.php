<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\Resolver;

final class ShortcutsCommand implements Command
{
    public function usage(): string
    {
        return 'pa shortcuts';
    }

    public function execute(Context $context, array $arguments): int
    {
        $context->expectCount($arguments, 0, $this->usage());
        $commands = $context->commands();
        $index = (new Resolver())->abbreviations($commands);
        $labels = [];
        foreach (Resolver::DEFAULTS as $shortcut => $command) {
            $alternatives = array_values(array_diff($index[$shortcut] ?? [], [$command]));
            $index[$shortcut] = [$command];
            $labels[$shortcut] = in_array($command, $commands, true) ? ' [FIXED]' : ' [UNAVAILABLE]';
            if ($alternatives !== []) {
                $labels[$shortcut] .= ' [use full names: ' . implode(' | ', $alternatives) . ']';
            }
        }
        foreach ($context->aliases->all() as $shortcut => $target) {
            if (
                !isset(Resolver::DEFAULTS[$shortcut]) && !in_array($shortcut, $commands, true)
                && !in_array($shortcut, $context->reservedNames, true)
            ) {
                $index[$shortcut] = [json_encode($target, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
                $labels[$shortcut] = in_array($target[0], $commands, true) ? ' [CUSTOM]' : ' [MISSING TARGET]';
            }
        }
        ksort($index, SORT_STRING);
        foreach ($index as $shortcut => $matches) {
            $label = $labels[$shortcut] ?? (count($matches) > 1 ? ' [AMBIGUOUS]' : '');
            if (!isset(Resolver::DEFAULTS[$shortcut]) && in_array($shortcut, $commands, true)) {
                $label .= ' [exact command takes precedence]';
            }
            fwrite(STDOUT, $shortcut . "\t" . implode(' | ', $matches) . $label . "\n");
        }
        return 0;
    }
}
