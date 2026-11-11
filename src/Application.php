<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use Throwable;

final class Application
{
    /** @param list<string> $arguments */
    public function run(array $arguments, string $binary = 'pa'): int
    {
        try {
            return $this->dispatch(new Options($arguments), $binary);
        } catch (ShortcutException $error) {
            fwrite(STDERR, 'Artisan Shortcuts: ' . $error->getMessage() . "\n");
            return 2;
        } catch (Throwable $error) {
            fwrite(STDERR, 'Artisan Shortcuts: ' . $error->getMessage() . "\nRun pa doctor for diagnostics.\n");
            return 1;
        }
    }

    private function dispatch(Options $options, string $binary): int
    {
        $arguments = $options->arguments;
        $isShortcut = isset(Resolver::DEFAULTS[$binary]);
        $showHelp = $arguments === [] || in_array($arguments[0], ['help', '--help', '-h'], true);
        if (!$isShortcut && !$options->direct && $showHelp) {
            fwrite(STDOUT, $this->help());
            return 0;
        }
        if (!$isShortcut && !$options->direct && $arguments === ['--version']) {
            fwrite(STDOUT, "Artisan Shortcuts " . Version::CURRENT . "\n");
            return 0;
        }
        $project = Project::find($options->project);
        $invocation = new Invocation($project, $options);
        $process = new Process();
        $store = new AliasStore($project);
        if ($options->direct && !$isShortcut) {
            if ($arguments === []) {
                throw new ShortcutException('Expected an Artisan command after --.');
            }
            return $process->run($invocation->command($arguments, true), $project);
        }
        $name = $isShortcut ? $binary : array_shift($arguments);
        if ($name === 'alias:list') {
            $this->arity($arguments, 0, 'pa alias:list');
            $json = json_encode(
                (object) $store->all(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );
            fwrite(STDOUT, $json . "\n");
            return 0;
        }
        if ($name === 'alias:remove') {
            $this->arity($arguments, 1, 'pa alias:remove NAME');
            $store->remove($arguments[0]);
            fwrite(STDOUT, "Alias removed.\n");
            return 0;
        }
        $commands = (new CommandCatalog())->discover($invocation, $process, $project);
        if ($name === 'doctor') {
            $this->arity($arguments, 0, 'pa doctor');
            $store->all();
            fwrite(
                STDOUT,
                "Project: {$project}\nPHP: " . PHP_VERSION . "\nPHP binary: {$options->php}\n"
                . "Runner: {$options->runner}\nCommands: " . count($commands)
                . "\nAlias file: valid\nDiscovery: OK\n"
            );
            return 0;
        }
        if ($name === 'alias:set') {
            if (count($arguments) < 2) {
                throw new ShortcutException('Usage: pa alias:set NAME COMMAND [ARGUMENTS...]');
            }
            $alias = array_shift($arguments);
            $store->set($alias, $arguments, $commands);
            fwrite(STDOUT, "Alias {$alias} saved.\n");
            return 0;
        }
        $resolver = new Resolver();
        if ($name === 'shortcuts') {
            $this->arity($arguments, 0, 'pa shortcuts');
            $index = $resolver->abbreviations($commands);
            foreach (Resolver::DEFAULTS as $shortcut => $command) {
                $index[$shortcut] = [$command];
            }
            foreach ($store->all() as $shortcut => $target) {
                if (
                    !isset(Resolver::DEFAULTS[$shortcut])
                    && !in_array($shortcut, $commands, true)
                    && !in_array($shortcut, Resolver::INTERNAL, true)
                ) {
                    $index[$shortcut] = [json_encode($target, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)];
                }
            }
            ksort($index, SORT_STRING);
            foreach ($index as $shortcut => $matches) {
                $label = count($matches) > 1 ? ' [AMBIGUOUS]' : '';
                fwrite(STDOUT, $shortcut . "\t" . implode(' | ', $matches) . $label . "\n");
            }
            return 0;
        }
        if ($name === null || str_starts_with($name, '-')) {
            throw new ShortcutException(
                'Expected a command. Wrapper options go before it; Artisan options go after it.'
            );
        }
        $target = $resolver->resolve($name, $commands, $store->all());
        return $process->run($invocation->command([...$target, ...$arguments], true), $project);
    }

    /** @param list<string> $arguments */
    private function arity(array $arguments, int $expected, string $usage): void
    {
        if (count($arguments) !== $expected) {
            throw new ShortcutException("Usage: {$usage}");
        }
    }

    private function help(): string
    {
        return <<<'HELP'
Artisan Shortcuts — safe shortcuts for your application's Artisan commands

Usage: pa [WRAPPER OPTIONS] COMMAND [ARTISAN ARGUMENTS/OPTIONS...]
       pa [WRAPPER OPTIONS] -- EXACT_ARTISAN_COMMAND [ARGUMENTS...]
       pas|pat|pad|paup|pam|pamf|pamm|pamc|parl|paoc [WRAPPER OPTIONS] [ARGUMENTS...]

  shortcuts                      Discover available shortcuts and collisions
  doctor                         Check project, runner, discovery and aliases
  alias:set NAME COMMAND [ARGS]  Create or replace a project alias
  alias:remove NAME              Remove a project alias
  alias:list                     Print project aliases as JSON
  help / --help                  Show this screen (pa -- help for Artisan help)
  --version                      Show package version

  --project PATH                 Find Laravel from this directory (default: cwd)
  --php PATH                     PHP executable for the local runner
  --runner local|sail|docker      Execution environment (default: local)
  --container NAME               Docker container name or ID
  --container-workdir PATH       Docker project path (default: /var/www/html)

Fixed shortcuts:
  pas serve    pat tinker    pad down    paup up    pam migrate
  pamf migrate:fresh    pamm make:model    pamc make:controller
  parl route:list    paoc optimize:clear

Dynamic: pa + first letter of each colon/hyphen/underscore-separated word.
Example: queue:work -> paqw; run `pa paqw`. Ambiguity always stops execution.
Exact command names and fixed shortcuts take precedence over custom aliases;
custom aliases take precedence over generated shortcuts. No implicit --force.

HELP;
    }
}
