<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\AliasStore;
use ArtisanShortcuts\ComposerTools;
use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ComposerToolsTest extends TestCase
{
    public function testCustomBinDirectoryAndLiteralArguments(): void
    {
        mkdir($this->directory . '/tool path', 0700, true);
        file_put_contents($this->directory . '/composer.json', '{"config":{"bin-dir":"tool path"}}');
        $path = $this->directory . '/tool path/pest';
        file_put_contents($path, "#!/usr/bin/env php\n<?php echo 'tool';");
        self::assertSame($path, ComposerTools::path($this->directory, 'pest'));
        self::assertSame(
            [PHP_BINARY, $path, '--filter=a b', '$(touch PWNED)'],
            (new Invocation($this->directory, new Options([])))->tool('pest', ['--filter=a b', '$(touch PWNED)'])
        );
    }

    public function testConfiguredVendorDirectoryIsHonored(): void
    {
        file_put_contents($this->directory . '/composer.json', '{"config":{"vendor-dir":"dependencies"}}');
        self::assertSame('dependencies/bin', ComposerTools::directory($this->directory));
    }

    public static function invalidNames(): array
    {
        return array_map(static fn ($name) => [$name], ['../pest', '/pest', '--bad', 'x;touch', 'x y', "x\0"]);
    }

    #[DataProvider('invalidNames')]
    public function testUnsafeNamesAreRejected(string $name): void
    {
        $this->expectException(ShortcutException::class);
        ComposerTools::validateName($name);
    }

    public function testNonPhpBinaryIsNotRunThroughAShell(): void
    {
        mkdir($this->directory . '/vendor/bin', 0700, true);
        file_put_contents($this->directory . '/vendor/bin/pest', "#!/bin/sh\necho unsafe");
        $this->expectException(ShortcutException::class);
        ComposerTools::path($this->directory, 'pest');
    }

    public function testMissingToolExplainsInstallationIsRequired(): void
    {
        $this->expectException(ShortcutException::class);
        $this->expectExceptionMessage('not installed');
        ComposerTools::path($this->directory, 'pest');
    }

    public function testMalformedComposerJsonFailsClearly(): void
    {
        file_put_contents($this->directory . '/composer.json', '{');
        $this->expectException(ShortcutException::class);
        ComposerTools::directory($this->directory);
    }

    public function testDockerToolUsesContainerPhpWithoutRequiringHostBinary(): void
    {
        $invocation = new Invocation($this->directory, new Options(['--runner=docker', '--container=app']));
        self::assertSame(
            ['docker', 'exec', '--workdir', '/var/www/html', 'app', 'php', 'vendor/bin/pest', '--filter=a b'],
            $invocation->tool('pest', ['--filter=a b'])
        );
    }

    public function testToolShortcutCannotBeOverwrittenByCustomAlias(): void
    {
        $this->expectException(ShortcutException::class);
        (new AliasStore($this->directory))->set('papest', ['make:model'], ['make:model']);
    }
}
