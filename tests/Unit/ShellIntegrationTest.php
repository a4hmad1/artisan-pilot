<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ShellIntegration;
use ArtisanShortcuts\ShortcutException;
use PHPUnit\Framework\TestCase;

final class ShellIntegrationTest extends TestCase
{
    public function testFixedUniqueAndCustomNamesAreExportedAndAmbiguitiesAreOmitted(): void
    {
        $names = (new ShellIntegration())->names(
            ['serve', 'migrate', 'queue:work', 'queue:watch', 'pail', 'make:model'],
            ['logs' => ['pail'], 'unsafe-name' => ['pail'], 'stale' => ['missing']],
            Resolver::INTERNAL
        );
        foreach (array_keys(Resolver::DEFAULTS) as $name) {
            self::assertContains($name, $names);
        }
        self::assertContains('pap', $names);
        self::assertContains('logs', $names);
        self::assertNotContains('paqw', $names);
        self::assertNotContains('unsafe-name', $names);
        self::assertNotContains('stale', $names);
    }

    public function testExplicitCustomAliasCanResolveADynamicCollision(): void
    {
        self::assertContains('paqw', (new ShellIntegration())->names(
            ['queue:work', 'queue:watch'],
            ['paqw' => ['queue:work', '--once']],
            Resolver::INTERNAL
        ));
    }

    public function testShellNamesCannotInjectCode(): void
    {
        $this->expectException(ShortcutException::class);
        (new ShellIntegration())->render('bash', ['x;touch PWNED'], '/a/pa', '/php');
    }

    public function testUnsupportedShellFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new ShellIntegration())->render('unknown', ['pas'], '/a/pa', '/php');
    }

    public function testFishAndPowershellOutputPreservesArgumentArrays(): void
    {
        $integration = new ShellIntegration();
        $fish = $integration->render('fish', ['pas'], "/a path/it's/pa", '/php');
        self::assertStringContainsString('function pas', $fish);
        self::assertStringContainsString('$argv', $fish);
        $powershell = $integration->render('powershell', ['pas'], "C:/a path/it's/pa", 'C:/php.exe');
        self::assertStringContainsString("'C:/a path/it''s/pa'", $powershell);
        self::assertStringContainsString('@args', $powershell);
    }
}
