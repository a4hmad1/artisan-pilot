<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class Invocation
{
    public function __construct(private readonly string $project, private readonly Options $options)
    {
    }

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function command(array $arguments, bool $interactive = false): array
    {
        if ($this->options->runner === 'sail') {
            return $this->sail(['artisan', ...$arguments]);
        }
        if ($this->options->runner === 'docker') {
            $container = $this->options->container;
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]*$/D', $container)) {
                throw new ShortcutException('Docker requires --container NAME (a container name or ID).');
            }
            $tty = $interactive && stream_isatty(STDIN) && stream_isatty(STDOUT);
            return [
                'docker', 'exec', ...($interactive ? ['-i'] : []), ...($tty ? ['-t'] : []),
                '--workdir', $this->options->containerWorkdir, $container, 'php', 'artisan', ...$arguments,
            ];
        }
        return [$this->options->php, $this->project . '/artisan', ...$arguments];
    }
    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function sail(array $arguments): array
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
