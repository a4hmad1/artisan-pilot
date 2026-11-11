<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Execution;

interface Runner
{
    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function artisan(array $arguments, bool $interactive): array;

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function tool(string $name, array $arguments, bool $interactive): array;
}
