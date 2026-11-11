<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/artisan-shortcuts-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0700, true);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    protected function cli(
        array $arguments,
        string $binary = 'pa',
        string $input = '',
        ?string $cwd = null,
        array $phpOptions = []
    ): array {
        $command = [PHP_BINARY, ...$phpOptions, dirname(__DIR__) . '/bin/' . $binary, ...$arguments];
        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd ?? $this->directory,
            null,
            ['bypass_shell' => true]
        );
        self::assertIsResource($process);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), $output, $errors];
    }
}
