<?php

declare(strict_types=1);

namespace ArtisanShortcuts;

use ArtisanShortcuts\Console\CommandRegistry;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\Console\Help;
use Throwable;

final class Application
{
    public function __construct(private readonly ?CommandRegistry $registry = null)
    {
    }

    /** @param list<string> $arguments */
    public function run(array $arguments, string $binary = 'pilot'): int
    {
        try {
            return $this->dispatch(new Options($arguments), $binary);
        } catch (ShortcutException $error) {
            fwrite(STDERR, 'Artisan Pilot: ' . $error->getMessage() . "\n");
            return 2;
        } catch (Throwable $error) {
            fwrite(STDERR, 'Artisan Pilot: ' . $error->getMessage() . "\nRun pilot doctor for diagnostics.\n");
            return 1;
        }
    }

    private function dispatch(Options $options, string $binary): int
    {
        $arguments = $options->arguments;
        $isShortcut = isset(Resolver::DEFAULTS[$binary]);
        $registry = $this->registry ?? CommandRegistry::defaults();
        if (!$isShortcut && !$options->direct) {
            if ($arguments === [] || in_array($arguments, [['help'], ['--help'], ['-h']], true)) {
                fwrite(STDOUT, Help::text());
                return 0;
            }
            if ($arguments === ['--version']) {
                fwrite(STDOUT, 'Artisan Pilot ' . Version::CURRENT . "\n");
                return 0;
            }
            if (count($arguments) === 2 && $arguments[0] === 'help') {
                $handler = $registry->get($arguments[1]);
                if ($handler !== null) {
                    fwrite(STDOUT, 'Usage: ' . $handler->usage() . "\n");
                    return 0;
                }
            }
        }
        $name = $isShortcut ? $binary : array_shift($arguments);
        if (!$options->direct && ($name === null || str_starts_with($name, '-'))) {
            throw new ShortcutException(
                'Expected a command. Wrapper options go before it; Artisan options go after it.'
            );
        }
        $handler = $name === null ? null : $registry->get($name);
        if (!$isShortcut && !$options->direct && $name !== 'sail' && $arguments === ['--help'] && $handler !== null) {
            fwrite(STDOUT, 'Usage: ' . $handler->usage() . "\n");
            return 0;
        }
        $project = Project::find($options->project);
        $invocation = new Invocation($project, $options);
        $process = new Process();
        if ($options->direct && !$isShortcut) {
            if ($name === null) {
                throw new ShortcutException('Expected an Artisan command after --.');
            }
            return $process->run($invocation->command([$name, ...$arguments], true), $project);
        }
        if ($name === null) {
            throw new ShortcutException('Expected a command.');
        }
        $reserved = ['help', ...$registry->names()];
        $store = new AliasStore($project, $reserved);
        $context = new Context($project, $options, $invocation, $process, $store, $reserved);
        if ($name === 'help') {
            return $process->run($invocation->command(['help', ...$arguments], true), $project);
        }
        if ($handler !== null && !$isShortcut) {
            return $handler->execute($context, $arguments);
        }
        $target = (new Resolver())->resolve($name, $context->commands(), $store->all());
        return $process->run($invocation->command([...$target, ...$arguments], true), $project);
    }
}
