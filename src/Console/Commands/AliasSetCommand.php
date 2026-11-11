<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShortcutException;

final class AliasSetCommand implements Command
{
    public function usage(): string
    {
        return 'pa alias:set NAME COMMAND [ARGUMENTS...]';
    }

    public function execute(Context $context, array $arguments): int
    {
        if (count($arguments) < 2) {
            throw new ShortcutException('Usage: ' . $this->usage());
        }
        $alias = array_shift($arguments);
        $context->aliases->set($alias, $arguments, $context->commands());
        fwrite(STDOUT, "Alias {$alias} saved.\n");
        return 0;
    }
}
