<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/src/Version.php';
$version = \ArtisanShortcuts\Version::CURRENT;
$target = $root . '/dist';
if (!extension_loaded('zip')) {
    fwrite(STDERR, "The ZIP extension is required.\n");
    exit(1);
}
$process = proc_open(
    ['git', 'ls-files', '-z'],
    [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR],
    $pipes,
    $root,
    null,
    ['bypass_shell' => true]
);
if (!is_resource($process)) {
    throw new RuntimeException('Cannot list tracked source files.');
}
$output = stream_get_contents($pipes[1]);
fclose($pipes[1]);
if (proc_close($process) !== 0 || $output === false || $output === '') {
    throw new RuntimeException('Track the release source in Git before building a ZIP.');
}
if (!is_dir($target)) {
    mkdir($target, 0777, true);
}
$filename = "artisan-pilot-{$version}.zip";
$path = $target . '/' . $filename;
$zip = new ZipArchive();
if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException('Cannot create release archive.');
}
foreach (explode("\0", rtrim($output, "\0")) as $file) {
    if (preg_match('~(^|/)(vendor|storage|dist|build|\.git|\.phpunit\.cache|\.phpstan)(/|$)|composer\.lock$|\.artisan-shortcuts\.json~', $file)) {
        throw new RuntimeException("Unexpected generated/private tracked file: {$file}");
    }
    if (!$zip->addFile($root . '/' . $file, 'artisan-pilot/' . $file)) {
        throw new RuntimeException("Cannot archive {$file}.");
    }
    $mode = str_starts_with($file, 'bin/') && $file !== 'bin/bootstrap.php' ? 0100755 : 0100644;
    $zip->setExternalAttributesName('artisan-pilot/' . $file, ZipArchive::OPSYS_UNIX, $mode << 16);
}
if (!$zip->close()) {
    throw new RuntimeException('Cannot finish archive.');
}
file_put_contents($path . '.sha256', hash_file('sha256', $path) . '  ' . $filename . "\n");
echo $path . "\n";
