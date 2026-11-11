<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console;

interface Command
{
    public function usage(): string;

    /** @param list<string> $arguments */
    public function execute(Context $context, array $arguments): int;
}
