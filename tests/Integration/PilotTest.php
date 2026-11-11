<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Integration;

use ArtisanShortcuts\Tests\TestCase;

final class PilotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        copy(dirname(__DIR__) . '/Fixtures/artisan', $this->directory . '/artisan');
    }

    public function testPilotAndPaResolveTheSameArtisanCommands(): void
    {
        foreach (['pilot', 'pa'] as $binary) {
            [$status, $output, $error] = $this->cli(['make:model', 'Post', '--migration'], $binary);
            self::assertSame(0, $status, $error);
            self::assertSame(['make:model', 'Post', '--migration'], json_decode($output, true));
        }
        self::assertSame(['make:model', 'Post'], json_decode($this->cli(['pamm', 'Post'], 'pilot')[1], true));
    }

    public function testPailAndOtherPackageCommandsNeedNoWrapperHardcoding(): void
    {
        foreach (['pail', 'horizon', 'sail:install', 'plugin:task'] as $command) {
            [$status, $output, $error] = $this->cli([$command, '--some-option=literal space'], 'pilot');
            self::assertSame(0, $status, $error);
            self::assertSame([$command, '--some-option=literal space'], json_decode($output, true));
        }
        self::assertSame(
            ['pail', '--filter=ERROR'],
            json_decode($this->cli(['pap', '--filter=ERROR'], 'pilot')[1], true)
        );
    }

    public function testCommandsCanBeListedFilteredAndReadAsJson(): void
    {
        [$status, $output, $error] = $this->cli(['commands', 'make', '--json'], 'pilot');
        self::assertSame(0, $status, $error);
        self::assertSame(['make:controller', 'make:middleware', 'make:model'], json_decode($output, true));
        [$status, $output, $error] = $this->cli(['commands', 'pail'], 'pilot');
        self::assertSame(0, $status, $error);
        self::assertStringContainsString('pail', $output);
        self::assertSame(2, $this->cli(['commands', 'uninstalled'], 'pilot')[0]);
        self::assertSame(2, $this->cli(['commands', '--wrong'], 'pilot')[0]);
        self::assertSame(2, $this->cli(['commands', 'make', 'extra'], 'pilot')[0]);
    }

    public function testFixedShortcutShowsShadowedGeneratedCommands(): void
    {
        $output = $this->cli(['shortcuts'], 'pilot')[1];
        self::assertStringContainsString('make:middleware', $output);
        self::assertStringContainsString('[FIXED]', $output);
        self::assertSame(['make:model'], json_decode($this->cli(['pamm'], 'pilot')[1], true));
    }

    public function testManagementHelpWorksOutsideLaravelAndNativeHelpIsForwarded(): void
    {
        self::assertSame(['help', 'make:model'], json_decode($this->cli(['help', 'make:model'], 'pilot')[1], true));
        unlink($this->directory . '/artisan');
        foreach ([['--help'], ['help'], ['help', 'commands'], ['commands', '--help'], ['--version']] as $args) {
            [$status, $output, $error] = $this->cli($args, 'pilot');
            self::assertSame(0, $status, $error);
            self::assertNotSame('', $output);
        }
    }

    public function testExistingAliasFileWorksAcrossEntrypoints(): void
    {
        self::assertSame(0, $this->cli(['alias:set', 'mylogs', 'pail', '--filter=ERROR'], 'pa')[0]);
        self::assertSame(['pail', '--filter=ERROR'], json_decode($this->cli(['mylogs'], 'pilot')[1], true));
        self::assertFileExists($this->directory . '/.artisan-shortcuts.json');
        foreach (['pilot', 'commands', 'sail'] as $reserved) {
            self::assertSame(2, $this->cli(['alias:set', $reserved, 'pail'], 'pilot')[0]);
        }
    }

    #[\PHPUnit\Framework\Attributes\Group('unix-sail')]
    public function testSailIsForwardedWithoutBootingArtisanAndPreservesArgumentsAndExit(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Sail uses Bash and is supported on Windows via WSL.');
        }
        mkdir($this->directory . '/vendor/bin', 0700, true);
        $php = escapeshellarg(PHP_BINARY);
        $fixture = escapeshellarg(dirname(__DIR__) . '/Fixtures/artisan');
        $script = "#!/usr/bin/env bash\nexec {$php} {$fixture} \"\$@\"\n";
        file_put_contents($this->directory . '/vendor/bin/sail', $script);
        file_put_contents($this->directory . '/artisan', '<?php exit(99);');
        [$status, $output, $error] = $this->cli(['sail', 'up', '-d', 'literal space', '$(touch PWNED)'], 'pilot');
        self::assertSame(0, $status, $error);
        self::assertSame(['up', '-d', 'literal space', '$(touch PWNED)'], json_decode($output, true));
        self::assertSame(42, $this->cli(['sail', 'probe:exit'], 'pilot')[0]);
        self::assertFileDoesNotExist($this->directory . '/PWNED');
        self::assertSame(2, $this->cli(['--runner=docker', 'sail', 'up'], 'pilot')[0]);
    }

    #[\PHPUnit\Framework\Attributes\Group('unix-sail')]
    public function testArtisanThroughSailAddsArtisanExactlyOnce(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Sail uses Bash and is supported on Windows via WSL.');
        }
        mkdir($this->directory . '/vendor/bin', 0700, true);
        $php = escapeshellarg(PHP_BINARY);
        $fixture = escapeshellarg(dirname(__DIR__) . '/Fixtures/artisan');
        $script = "#!/usr/bin/env bash\n[ \"\$1\" = artisan ] || exit 98\nshift\nexec {$php} {$fixture} \"\$@\"\n";
        file_put_contents($this->directory . '/vendor/bin/sail', $script);
        [$status, $output, $error] = $this->cli(['--runner=sail', 'pail', '--filter=ERROR'], 'pilot');
        self::assertSame(0, $status, $error);
        self::assertSame(['pail', '--filter=ERROR'], json_decode($output, true));
    }
}
