<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Execution;

use ArtisanShortcuts\ComposerTools;

final readonly class LocalRunner implements Runner
{
    public function __construct(private string $project, private string $php)
    {
    }

    public function artisan(array $arguments, bool $interactive): array
    {
        return [$this->php, $this->project . '/artisan', ...$arguments];
    }

    public function tool(string $name, array $arguments, bool $interactive): array
    {
        return [$this->php, ComposerTools::path($this->project, $name), ...$arguments];
    }
}
