<?php

declare(strict_types=1);

$mode = $argv[1] ?? 'pest';
$major = $argv[2] ?? '13';
if (!in_array($mode, ['phpunit', 'pest'], true) || !in_array($major, ['11', '12', '13'], true)) {
    fwrite(STDERR, "Usage: php tools/test-runners-smoke.php phpunit|pest 11|12|13\n");
    exit(2);
}
$root = dirname(__DIR__);
$project = $root . '/build/runners/' . $mode . '-' . $major;
$logDirectory = $root . '/build/qa/runners/' . $mode . '-' . $major;
if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0777, true);
}
$results = [];
$run = static function (string $label, string $binary, array $arguments, int $expected = 0, bool $failure = false) use ($root, $project, $logDirectory, &$results): string {
    $previous = getenv('ARTISAN_SHORTCUTS_TEST_FAILURE');
    putenv('ARTISAN_SHORTCUTS_TEST_FAILURE=' . ($failure ? '1' : '0'));
    try {
        $command = [PHP_BINARY, $root . '/bin/' . $binary, '--project', $project, ...$arguments];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $logDirectory . '/' . $label . '.stdout', 'w'], 2 => ['file', $logDirectory . '/' . $label . '.stderr', 'w']], $pipes, $project, null, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            throw new RuntimeException('Cannot start ' . $label);
        }
        fclose($pipes[0]);
        $exit = proc_close($process);
    } finally {
        putenv($previous === false ? 'ARTISAN_SHORTCUTS_TEST_FAILURE' : 'ARTISAN_SHORTCUTS_TEST_FAILURE=' . $previous);
    }
    $output = file_get_contents($logDirectory . '/' . $label . '.stdout') . file_get_contents($logDirectory . '/' . $label . '.stderr');
    $results[] = ['case' => $label, 'exit' => $exit, 'expected' => $expected, 'passed' => $exit === $expected];
    if ($exit !== $expected) {
        throw new RuntimeException($label . ' returned ' . $exit . ', expected ' . $expected . ":\n" . $output);
    }
    echo $label . ': exit ' . $exit . " OK\n";
    return $output;
};
$filter = $mode === 'pest' ? 'pilot_pest_case' : 'PilotPassingTest';
$run('artisan-test-pass', 'patt', ['--filter=' . $filter, '--no-ansi']);
$run('artisan-test-fail', 'patt', ['--filter=' . $filter, '--no-ansi'], 1, true);
$run('artisan-test-parallel', 'patt', ['--parallel', '--processes=2', '--filter=' . $filter, '--no-ansi']);
$binary = $mode === 'pest' ? 'papest' : 'papu';
$tool = $mode === 'pest' ? 'pest' : 'phpunit';
$run('direct-tool-pass', $binary, ['--filter=' . $filter, '--no-configuration', 'tests/Unit/' . ($mode === 'pest' ? 'PilotPestTest.php' : 'PilotPassingTest.php')]);
$run('direct-tool-fail', $binary, ['--filter=' . $filter], 1, true);
$run('generic-tool', 'pa', ['bin', $tool, '--filter=' . $filter]);
$run('pint-check', 'papint', ['--test', '--preset=psr12', 'tests/Unit/PilotPassingTest.php']);
if ($mode === 'pest') {
    $run('pest-parallel', 'papest', ['--parallel', '--processes=2', '--filter=' . $filter]);
}
$packages = json_decode(file_get_contents($project . '/vendor/composer/installed.json'), true, 512, JSON_THROW_ON_ERROR);
$versions = [];
foreach ($packages['packages'] as $package) {
    if (in_array($package['name'], ['laravel/framework', 'pestphp/pest', 'phpunit/phpunit', 'brianium/paratest', 'nunomaduro/collision', 'laravel/pint'], true)) {
        $versions[$package['name']] = $package['version'];
    }
}
file_put_contents($logDirectory . '/report.json', json_encode(['mode' => $mode, 'php' => PHP_VERSION, 'versions' => $versions, 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo 'All ' . count($results) . ' runner checks passed.' . "\n";
