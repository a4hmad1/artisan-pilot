<?php

declare(strict_types=1);

$mode = $argv[1] ?? 'pest';
$major = $argv[2] ?? '13';
if (!in_array($mode, ['phpunit', 'pest'], true) || !in_array($major, ['11', '12', '13'], true)) {
    fwrite(STDERR, "Usage: php tools/prepare-test-runner.php phpunit|pest 11|12|13\n");
    exit(2);
}
$root = dirname(__DIR__);
$source = $root . '/tests/laravel';
$project = $root . '/build/runners/' . $mode . '-' . $major;
foreach (['bootstrap/cache', 'app/Console/Commands', 'tests/Unit', 'storage/framework/views', 'storage/framework/cache', 'storage/framework/sessions', 'storage/logs'] as $directory) {
    if (!is_dir($project . '/' . $directory)) {
        mkdir($project . '/' . $directory, 0777, true);
    }
}
$data = json_decode(file_get_contents($source . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$data['name'] = 'artisan-shortcuts/' . $mode . '-runner-fixture';
$data['require']['laravel/framework'] = '^' . $major . '.0';
$data['repositories'][0]['url'] = $root;
if ($mode === 'pest') {
    unset($data['require-dev']['phpunit/phpunit'], $data['require-dev']['brianium/paratest']);
    $data['require-dev']['pestphp/pest'] = '^3.8 || ^4.0 || ^5.0';
    $data['config']['allow-plugins'] = ['pestphp/pest-plugin' => true];
}
file_put_contents($project . '/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
foreach (['artisan', 'bootstrap/app.php', 'bootstrap/providers.php', 'phpunit.xml', 'app/Console/Commands/ProbeCommand.php', 'tests/Unit/PilotPassingTest.php'] as $file) {
    copy($source . '/' . $file, $project . '/' . $file);
}
if ($mode === 'pest') {
    file_put_contents($project . '/tests/Pest.php', "<?php\n\n// Runner fixture: no application state or external services are required.\n");
    file_put_contents($project . '/tests/Unit/PilotPestTest.php', <<<'PEST'
<?php

test('pilot_pest_case', function () {
    expect(getenv('ARTISAN_SHORTCUTS_TEST_FAILURE'))->not->toBe('1');
});

PEST);
}
echo $project . "\n";
