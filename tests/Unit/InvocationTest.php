<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Execution\Runner;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;

final class InvocationTest extends TestCase
{
    public function testRunnerCanBeReplacedWithoutChangingCommandDispatch(): void
    {
        $runner = $this->createMock(Runner::class);
        $runner->expects(self::once())->method('artisan')
            ->with(['make:model', 'a;echo hacked'], true)
            ->willReturn(['custom-runtime', 'artisan', 'make:model', 'a;echo hacked']);
        $runner->expects(self::once())->method('tool')
            ->with('pest', ['--filter=a b'], false)
            ->willReturn(['custom-runtime', 'pest', '--filter=a b']);
        $invocation = new Invocation($this->directory, new Options([]), $runner);

        self::assertSame(
            ['custom-runtime', 'artisan', 'make:model', 'a;echo hacked'],
            $invocation->command(['make:model', 'a;echo hacked'], true)
        );
        self::assertSame(
            ['custom-runtime', 'pest', '--filter=a b'],
            $invocation->tool('pest', ['--filter=a b'])
        );
    }

    public function testRunnerConfigurationIsCapturedWhenInvocationIsCreated(): void
    {
        $options = new Options(['--php=/original/php']);
        $invocation = new Invocation($this->directory, $options);
        $options->runner = 'docker';
        $options->php = '/changed/php';

        self::assertSame(
            ['/original/php', $this->directory . '/artisan', 'list'],
            $invocation->command(['list'])
        );
    }

    public function testLocalUsesArgumentArray(): void
    {
        $invocation = new Invocation($this->directory, new Options(['--php=/a b/php']));
        self::assertSame(
            ['/a b/php', $this->directory . '/artisan', 'make:model', 'a;echo hacked'],
            $invocation->command(['make:model', 'a;echo hacked'])
        );
    }

    public function testDockerDiscoveryHasNoInteractiveFlags(): void
    {
        $options = new Options(['--runner=docker', '--container=app', '--container-workdir=/srv/app']);
        self::assertSame(
            ['docker', 'exec', '--workdir', '/srv/app', 'app', 'php', 'artisan', 'list'],
            (new Invocation($this->directory, $options))->command(['list'])
        );
    }

    public function testDockerRejectsOptionLikeContainer(): void
    {
        $this->expectException(ShortcutException::class);
        (new Invocation($this->directory, new Options(['--runner=docker', '--container=-x'])))->command(['list']);
    }

    public function testSailRequiresInstalledScript(): void
    {
        $this->expectException(ShortcutException::class);
        (new Invocation($this->directory, new Options(['--runner=sail'])))->command(['list']);
    }
}
