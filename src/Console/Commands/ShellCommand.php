<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\ShellIntegration;
use ArtisanShortcuts\ShortcutException;

final class ShellCommand implements Command
{
    public function usage(): string
    {
        return 'pa shell bash|zsh|fish|powershell';
    }

    public function execute(Context $context, array $arguments): int
    {
        $context->expectCount($arguments, 1, $this->usage());
        if (!in_array($arguments[0], ShellIntegration::SHELLS, true)) {
            throw new ShortcutException('Shell must be bash, zsh, fish, or powershell.');
        }
        $integration = new ShellIntegration();
        $names = $integration->names($context->commands(), $context->aliases->all(), $context->reservedNames);
        fwrite(STDOUT, $integration->render($arguments[0], $names, dirname(__DIR__, 3) . '/bin/pa', PHP_BINARY));
        return 0;
    }
}
