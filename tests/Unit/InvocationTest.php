<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;

final class InvocationTest extends TestCase
{
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
