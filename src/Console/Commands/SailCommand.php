<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShortcutException;

final class SailCommand implements Command
{
    public function usage(): string
    {
        return 'pilot sail [SAIL ARGUMENTS...]';
    }

    public function execute(Context $context, array $arguments): int
    {
        if ($context->options->runner === 'docker') {
            throw new ShortcutException('pilot sail runs the host Sail script. Omit --runner=docker.');
        }
        return $context->process->run($context->invocation->sail($arguments), $context->project);
    }
}
