<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Execution;

use ArtisanShortcuts\ComposerTools;
use ArtisanShortcuts\ShortcutException;

final readonly class SailRunner implements Runner
{
    public function __construct(private string $project)
    {
    }

    public function artisan(array $arguments, bool $interactive): array
    {
        return $this->command(['artisan', ...$arguments]);
    }

    public function tool(string $name, array $arguments, bool $interactive): array
    {
        ComposerTools::validateName($name);
        return $this->command(['bin', $name, ...$arguments]);
    }

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function command(array $arguments): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            throw new ShortcutException('Run Sail shortcuts in WSL, where Bash and Docker are available.');
        }
        $sail = $this->project . '/vendor/bin/sail';
        if (!is_file($sail)) {
            throw new ShortcutException('Sail is missing. Install laravel/sail or select --runner local.');
        }
        return ['bash', $sail, ...$arguments];
    }
}
