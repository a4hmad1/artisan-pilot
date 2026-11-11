<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Execution;

use ArtisanShortcuts\ComposerTools;
use ArtisanShortcuts\ShortcutException;

final readonly class DockerRunner implements Runner
{
    public function __construct(private string $project, private string $container, private string $workdir)
    {
    }

    public function artisan(array $arguments, bool $interactive): array
    {
        return $this->command(['php', 'artisan', ...$arguments], $interactive);
    }

    public function tool(string $name, array $arguments, bool $interactive): array
    {
        ComposerTools::validateName($name);
        $path = rtrim(ComposerTools::directory($this->project), '/') . '/' . $name;
        return $this->command(['php', $path, ...$arguments], $interactive);
    }

    /** @param non-empty-list<string> $arguments
     *  @return non-empty-list<string>
     */
    private function command(array $arguments, bool $interactive): array
    {
        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/D', $this->container)) {
            throw new ShortcutException('Docker requires --container NAME (a container name or ID).');
        }
        $tty = $interactive && stream_isatty(STDIN) && stream_isatty(STDOUT);
        return [
            'docker', 'exec', ...($interactive ? ['-i'] : []), ...($tty ? ['-t'] : []),
            '--workdir', $this->workdir, $this->container, ...$arguments,
        ];
    }
}
