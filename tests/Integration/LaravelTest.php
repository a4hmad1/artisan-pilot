<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Integration;

use ArtisanShortcuts\Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('real-laravel')]
final class LaravelTest extends TestCase
{
    private function fixture(): string
    {
        $fixture = dirname(__DIR__) . '/laravel';
        if (!is_file($fixture . '/vendor/autoload.php')) {
            self::markTestSkipped('Install tests/laravel dependencies to run real Laravel integration tests.');
        }
        return $fixture;
    }

    public function testRealBuiltInCustomAndThirdPartyCommandDiscovery(): void
    {
        $fixture = $this->fixture();
        [$status, $output, $error] = $this->cli(['--project', $fixture, 'shortcuts']);
        self::assertSame(0, $status, $error);
        self::assertStringContainsString('route:list', $output);
        self::assertStringContainsString('shortcuts:probe-check', $output);
        self::assertStringContainsString('tinker', $output);
        [$status, $output, $error] = $this->cli(['--project', $fixture, 'paspc', 'literal space']);
        self::assertSame(0, $status, $error);
        self::assertStringContainsString('probe:literal space', $output);
        self::assertSame(42, $this->cli(['--project', $fixture, 'shortcuts:probe-check', '--fail'])[0]);
    }

    public function testLaravelConfirmationAndNonInteractiveCancellation(): void
    {
        $fixture = $this->fixture();
        [$status, $output, $error] = $this->cli([
            '--project', $fixture, 'shortcuts:probe-check', '--confirm', '--no-interaction',
        ]);
        self::assertSame(3, $status, $error . $output);
        self::assertSame(
            0,
            $this->cli(['--project', $fixture, 'shortcuts:probe-check', '--confirm'], input: "yes\n")[0]
        );
    }

    public function testRealRouteListTinkerAndDatabaseMigration(): void
    {
        $fixture = $this->fixture();
        [$status, $output, $error] = $this->cli(['--project', $fixture, '--no-interaction'], 'pam');
        self::assertSame(0, $status, $error . $output);
        [$status, $output, $error] = $this->cli(['--project', $fixture, '--execute=echo "tinker-ok";'], 'pat');
        self::assertSame(0, $status, $error . $output);
        self::assertStringContainsString('tinker-ok', $output);
        [$status, $output, $error] = $this->cli(['--project', $fixture, '--json'], 'parl');
        self::assertSame(0, $status, $error . $output);
        self::assertStringContainsString('shortcuts-test', $output);
    }

    public function testComposerInstalledProxyBinaries(): void
    {
        $fixture = $this->fixture();
        $command = [PHP_BINARY, $fixture . '/vendor/bin/pa', '--project', $fixture, 'shortcuts:probe-check', 'proxy'];
        $process = new \ArtisanShortcuts\Process();
        self::assertStringContainsString('probe:proxy', $process->capture($command, $fixture));
        self::assertStringContainsString(
            'probe:direct',
            $process->capture(
                [
                    PHP_BINARY, $fixture . '/vendor/bin/pa', '--project', $fixture,
                    '--', 'shortcuts:probe-check', 'direct',
                ],
                $fixture
            )
        );
    }
    public function testAllDiscoveredCommandsResolveExactly(): void
    {
        $fixture = $this->fixture();
        $commands = (new \ArtisanShortcuts\CommandCatalog())->discover(
            new \ArtisanShortcuts\Invocation($fixture, new \ArtisanShortcuts\Options([])),
            new \ArtisanShortcuts\Process(),
            $fixture
        );
        self::assertGreaterThan(100, count($commands));
        $resolver = new \ArtisanShortcuts\Resolver();
        foreach ($commands as $command) {
            self::assertSame([$command], $resolver->resolve($command, $commands, []));
        }
    }
    public function testProductionMigrationConfirmationIsPreserved(): void
    {
        [$status, $output, $error] = $this->cli(
            ['--project', $this->fixture(), '--env=production', '--no-interaction'],
            'pam'
        );
        self::assertSame(1, $status, $error . $output);
        self::assertStringContainsString('cancelled', strtolower($output));
    }
}
