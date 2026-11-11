<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Integration;

use ArtisanShortcuts\Tests\TestCase;
use ArtisanShortcuts\ToolShortcuts;
use PHPUnit\Framework\Attributes\DataProvider;

final class ToolShortcutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Tool execution must not need Artisan discovery or a working Laravel bootstrap.
        file_put_contents($this->directory . '/artisan', '<?php exit(99);');
        mkdir($this->directory . '/vendor/bin', 0700, true);
        foreach (ToolShortcuts::DEFAULTS as $tool) {
            file_put_contents($this->directory . '/vendor/bin/' . $tool, <<<'TOOL'
<?php
if (in_array('--fail', $argv, true)) {
    exit(23);
}
if (in_array('--stdin', $argv, true)) {
    echo stream_get_contents(STDIN);
    fwrite(STDERR, 'tool stderr');
    exit(0);
}
echo json_encode(array_slice($argv, 1));
TOOL);
        }
    }

    public static function binaries(): array
    {
        return array_map(static fn ($name) => [$name], array_keys(ToolShortcuts::DEFAULTS));
    }

    #[DataProvider('binaries')]
    public function testBareToolArgumentsAndExitCodes(string $binary): void
    {
        [$status, $output, $error] = $this->cli(['--filter=literal space', '$(touch PWNED)', ''], $binary);
        self::assertSame(0, $status, $error);
        self::assertSame(['--filter=literal space', '$(touch PWNED)', ''], json_decode($output, true));
        self::assertSame(23, $this->cli(['--fail'], $binary)[0]);
        self::assertFileDoesNotExist($this->directory . '/PWNED');
    }

    public function testGenericToolCommandAndStreams(): void
    {
        [$status, $output, $error] = $this->cli(['bin', 'pest', '--stdin'], input: "stdin to pest\n");
        self::assertSame(0, $status);
        self::assertSame("stdin to pest\n", $output);
        self::assertSame('tool stderr', $error);
    }

    public function testMissingToolIsAnErrorNotAnInstallSideEffect(): void
    {
        unlink($this->directory . '/vendor/bin/pest');
        [$status, $output, $error] = $this->cli([], 'papest');
        self::assertSame(2, $status);
        self::assertStringContainsString('not installed', $error);
        self::assertSame('', $output);
    }
}
