<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;

final class AliasRemoveCommand implements Command
{
    public function usage(): string
    {
        return 'pa alias:remove NAME';
    }

    public function execute(Context $context, array $arguments): int
    {
        $context->expectCount($arguments, 1, $this->usage());
        $context->aliases->remove($arguments[0]);
        fwrite(STDOUT, "Alias removed.\n");
        return 0;
    }
}
