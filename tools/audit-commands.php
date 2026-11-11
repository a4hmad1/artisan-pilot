<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use ArtisanShortcuts\CommandCatalog;
use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\Process;
use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ToolShortcuts;

$root = dirname(__DIR__);
$project = realpath($argv[1] ?? $root . '/tests/laravel');
$label = $argv[2] ?? 'laravel';
if ($project === false || !is_file($project . '/artisan') || !preg_match('/^[a-zA-Z0-9_-]+$/D', $label)) {
    fwrite(STDERR, "Usage: php tools/audit-commands.php PROJECT LABEL\n");
    exit(2);
}
$directory = $root . '/build/qa/commands';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$process = new Process();
$invocation = new Invocation($project, new Options([]));
$commands = (new CommandCatalog())->discover($invocation, $process, $project);
$resolver = new Resolver();
$index = $resolver->abbreviations($commands);
$rows = [];
$failures = [];
$shortcutChecks = 0;
foreach ($commands as $position => $name) {
    $row = ['command' => $name, 'exact_resolution' => false, 'native_help' => false, 'shortcut' => null, 'shortcut_help' => null];
    try {
        if ($resolver->resolve($name, $commands, []) !== [$name]) {
            throw new RuntimeException('Exact command resolution changed meaning.');
        }
        $row['exact_resolution'] = true;
        $native = $process->capture([PHP_BINARY, $root . '/bin/pa', '--project', $project, '--', $name, '--help', '--no-ansi'], $project, 15.0);
        $row['native_help'] = true;
        $shortcut = array_search($name, Resolver::DEFAULTS, true);
        if ($shortcut === false) {
            foreach ($index as $candidate => $matches) {
                if (in_array($name, $matches, true)) {
                    if (count($matches) === 1 && !isset(Resolver::DEFAULTS[$candidate]) && !isset(ToolShortcuts::DEFAULTS[$candidate])) {
                        $shortcut = $candidate;
                    }
                    break;
                }
            }
        }
        if (is_string($shortcut) && preg_match('/^[a-z][a-z0-9_]*$/D', $shortcut)) {
            $output = $process->capture([PHP_BINARY, $root . '/bin/pa', '--project', $project, '--shortcut=' . $shortcut, '--help', '--no-ansi'], $project, 15.0);
            if (trim($output) !== trim($native)) {
                throw new RuntimeException('Shortcut help differs from native command help.');
            }
            $row['shortcut'] = $shortcut;
            $row['shortcut_help'] = true;
            $shortcutChecks++;
        }
    } catch (Throwable $error) {
        $row['error'] = $error->getMessage();
        $failures[] = $name;
    }
    $rows[] = $row;
    if (($position + 1) % 25 === 0) {
        echo ($position + 1) . '/' . count($commands) . " commands audited\n";
    }
}
$report = [
    'label' => $label,
    'php' => PHP_VERSION,
    'framework' => trim($process->capture($invocation->command(['--version']), $project)),
    'commands' => count($commands),
    'shortcut_help_checks' => $shortcutChecks,
    'failures' => $failures,
    'scope' => 'Exact resolution and help execution. Destructive and long-running handlers are not executed.',
    'results' => $rows,
];
file_put_contents($directory . '/' . $label . '.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo count($commands) . ' exact/help checks; ' . $shortcutChecks . ' shortcut/help checks; ' . count($failures) . " failures.\n";
if ($failures !== []) {
    fwrite(STDERR, implode(', ', $failures) . "\n");
    exit(1);
}
