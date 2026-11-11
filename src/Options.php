<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

final class Options
{
    public string $project;
    public string $php;
    public string $runner = 'local';
    public string $container = '';
    public string $containerWorkdir = '/var/www/html';
    /** @var list<string> */
    public array $arguments = [];
    public bool $direct = false;

    /** @param list<string> $arguments */
    public function __construct(array $arguments)
    {
        $this->project = getcwd() ?: '.';
        $this->php = PHP_BINARY;
        $names = [
            '--project' => 'project', '--php' => 'php', '--runner' => 'runner',
            '--container' => 'container', '--container-workdir' => 'containerWorkdir',
        ];
        while ($arguments !== []) {
            $argument = array_shift($arguments);
            if ($argument === '--') {
                $this->direct = true;
                break;
            }
            $parts = explode('=', $argument, 2);
            $name = $parts[0];
            $value = $parts[1] ?? null;
            if (!isset($names[$name])) {
                array_unshift($arguments, $argument);
                break;
            }
            $value ??= array_shift($arguments);
            if ($value === null || $value === '' || str_starts_with($value, '--') || str_contains($value, "\0")) {
                throw new ShortcutException("{$name} requires a non-empty value.");
            }
            $this->{$names[$name]} = $value;
        }
        if (!in_array($this->runner, ['local', 'sail', 'docker'], true)) {
            throw new ShortcutException('Runner must be local, sail, or docker.');
        }
        $this->arguments = $arguments;
    }
}
