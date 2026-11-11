<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use JsonException;

final class CommandCatalog
{
    /** @return list<string> */
    public function discover(Invocation $invocation, Process $process, string $project): array
    {
        $json = $process->capture(
            $invocation->command(['list', '--format=json', '--no-ansi', '--no-interaction']),
            $project
        );
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new ShortcutException(
                'Artisan returned invalid JSON. Remove boot-time output; try pilot -- list --format=json.'
            );
        }
        if (!is_array($data) || !isset($data['commands']) || !is_array($data['commands'])) {
            throw new ShortcutException('Artisan JSON has no commands array. Try pilot -- list --format=json.');
        }
        $commands = [];
        foreach ($data['commands'] as $command) {
            if (!is_array($command) || !isset($command['name']) || !is_string($command['name'])) {
                throw new ShortcutException('Artisan returned a malformed command record.');
            }
            $commands[] = $command['name'];
            $aliases = $command['aliases'] ?? [];
            if (!is_array($aliases)) {
                throw new ShortcutException('Artisan returned malformed command aliases.');
            }
            foreach ($aliases as $alias) {
                if (is_string($alias)) {
                    $commands[] = $alias;
                }
            }
        }
        $commands = array_values(array_unique($commands));
        sort($commands, SORT_STRING);
        return $commands;
    }
}
