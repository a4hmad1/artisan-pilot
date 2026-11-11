<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console\Commands;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\Context;

final class AliasListCommand implements Command
{
    public function usage(): string
    {
        return 'pilot alias:list';
    }

    public function execute(Context $context, array $arguments): int
    {
        $context->expectCount($arguments, 0, $this->usage());
        $json = json_encode(
            (object) $context->aliases->all(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        fwrite(STDOUT, $json . "\n");
        return 0;
    }
}
