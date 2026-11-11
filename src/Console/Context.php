<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Console;

use ArtisanShortcuts\AliasStore;
use ArtisanShortcuts\CommandCatalog;
use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\Process;
use ArtisanShortcuts\ShortcutException;

final class Context
{
    /** @var list<string>|null */
    private ?array $commands = null;

    /** @param list<string> $reservedNames */
    public function __construct(
        public readonly string $project,
        public readonly Options $options,
        public readonly Invocation $invocation,
        public readonly Process $process,
        public readonly AliasStore $aliases,
        public readonly array $reservedNames
    ) {
    }

    /** @return list<string> */
    public function commands(): array
    {
        return $this->commands ??= (new CommandCatalog())->discover($this->invocation, $this->process, $this->project);
    }

    /** @param list<string> $arguments */
    public function expectCount(array $arguments, int $expected, string $usage): void
    {
        if (count($arguments) !== $expected) {
            throw new ShortcutException('Usage: ' . $usage);
        }
    }
}
