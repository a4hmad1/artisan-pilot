<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root . '/docs/reports';
if (!is_dir($target)) {
    mkdir($target, 0777, true);
}
$audits = [];
foreach (['11', '12', '13'] as $major) {
    $source = $root . '/build/qa/commands/laravel' . $major . '.json';
    $report = json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
    if ($report['failures'] !== []) {
        throw new RuntimeException('Cannot publish a passing audit summary with failed commands.');
    }
    $audits[$major] = $report;
    copy($source, $target . '/commands-laravel' . $major . '.json');
}
$document = <<<'MD'
# Complete discovered command audit

These reports cover every command exposed by the real fixture application's Artisan JSON registry on Laravel 11, 12, and 13. Fixtures include application commands, Tinker, Pail, Sail, and Laravel's Collision test command. Pest/PHPUnit/Pint execution is checked separately below.

For each discovered command, the audit verifies exact name resolution and runs its native help through the package. Every available fixed or unambiguous shortcut is also checked against that native help output. Ambiguous and shadowed generated names are not silently executed.

**Help verification does not execute destructive handlers.** Database deletion, deployment, external notifications, interactive services, coverage, and arbitrary third-party plugins are not certified by a help scan. Hidden commands omitted by Artisan's JSON are available through raw mode but are outside this registry snapshot.

## Registry results

| Framework | Exact resolution + native help | Shortcut help comparisons | Failures | JSON evidence |
| --- | --- | --- | --- | --- |
MD;
foreach ($audits as $major => $report) {
    $document .= "\n| " . $report['framework'] . ' | ' . $report['commands'] . ' passed | '
        . $report['shortcut_help_checks'] . ' passed | 0 | [report](reports/commands-laravel' . $major . '.json) |';
}
$document .= "\n\n## Test runner execution\n\nThe following are actual test executions, including expected failing tests, filtering, parallel runs with two processes, direct tool entry points, and Pint checks.\n\n";
$document .= "| Laravel | Mode | Pest | PHPUnit | ParaTest | Cases | JSON evidence |\n| --- | --- | --- | --- | --- | --- | --- |\n";
foreach (['11', '12', '13'] as $major) {
    foreach (['phpunit', 'pest'] as $mode) {
        $source = $root . '/build/qa/runners/' . $mode . '-' . $major . '/report.json';
        $report = json_decode(file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
        foreach ($report['results'] as $result) {
            if (!$result['passed']) {
                throw new RuntimeException('Runner verification has a failed case.');
            }
        }
        $name = 'runner-' . $mode . '-laravel' . $major . '.json';
        copy($source, $target . '/' . $name);
        $versions = $report['versions'];
        $document .= '| ' . $versions['laravel/framework'] . ' | ' . $mode . ' | '
            . ($versions['pestphp/pest'] ?? 'not installed') . ' | ' . $versions['phpunit/phpunit']
            . ' | ' . $versions['brianium/paratest'] . ' | ' . count($report['results'])
            . ' passed | [report](reports/' . $name . ") |\n";
    }
}
$document .= "\nLocal reports use PHP " . PHP_VERSION . '. Laravel 11 is tested locally outside its published PHP 8.5 support range; supported PHP combinations are configured in CI. Hosted CI availability and unverified platforms are recorded in [verification evidence](verification.md).'
    . "\n\n## Full command lists\n\nA dash in the shortcut column means the generated name was ambiguous, shadowed, or unsuitable for shell export. Use the exact command through `pa COMMAND`. The live registry is always authoritative; these lists are versioned test snapshots, not hardcoded runtime mappings.\n";
foreach ($audits as $major => $report) {
    $document .= "\n<details>\n<summary>Laravel " . $major . ' — ' . $report['commands'] . " commands</summary>\n\n";
    $document .= "| Artisan command | Shortcut checked | Exact/help |\n| --- | --- | --- |\n";
    foreach ($report['results'] as $row) {
        $document .= '| `' . $row['command'] . '` | ' . ($row['shortcut'] === null ? '—' : '`' . $row['shortcut'] . '`') . " | Pass |\n";
    }
    $document .= "\n</details>\n";
}
file_put_contents($root . '/docs/command-support.md', $document);
echo 'Published ' . count($audits) . ' registry reports and six runner reports to docs.' . "\n";
