<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShortcutException;

final class BinCommand implements Command
{
    public function usage(): string
    {
        return 'pa bin NAME [TOOL ARGUMENTS...]';
    }

    public function execute(Context $context, array $arguments): int
    {
        $name = array_shift($arguments);
        if ($name === null) {
            throw new ShortcutException('Usage: ' . $this->usage());
        }
        return $context->process->run($context->invocation->tool($name, $arguments, true), $context->project);
    }
}
