<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use ArtisanShortcuts\Execution\DockerRunner;
use ArtisanShortcuts\Execution\LocalRunner;
use ArtisanShortcuts\Execution\Runner;
use ArtisanShortcuts\Execution\SailRunner;

final readonly class Invocation
{
    private Runner $runner;

    public function __construct(private string $project, Options $options, ?Runner $runner = null)
    {
        $this->runner = $runner ?? match ($options->runner) {
            'local' => new LocalRunner($project, $options->php),
            'sail' => new SailRunner($project),
            'docker' => new DockerRunner($project, $options->container, $options->containerWorkdir),
            default => throw new ShortcutException('Runner must be local, sail, or docker.'),
        };
    }

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function command(array $arguments, bool $interactive = false): array
    {
        return $this->runner->artisan($arguments, $interactive);
    }

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function sail(array $arguments): array
    {
        return (new SailRunner($this->project))->command($arguments);
    }

    /** @param list<string> $arguments
     *  @return non-empty-list<string>
     */
    public function tool(string $name, array $arguments, bool $interactive = false): array
    {
        ComposerTools::validateName($name);
        return $this->runner->tool($name, $arguments, $interactive);
    }
}
