<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Options;
use ArtisanShortcuts\ShortcutException;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    public function testWrapperParsingStopsAtCommand(): void
    {
        $options = new Options([
            '--project=/tmp/path with space', '--runner', 'docker', '--container', 'app',
            'probe', '--runner', 'a target option',
        ]);
        self::assertSame('/tmp/path with space', $options->project);
        self::assertSame('docker', $options->runner);
        self::assertSame('app', $options->container);
        self::assertSame(['probe', '--runner', 'a target option'], $options->arguments);
    }

    public function testRawMode(): void
    {
        $options = new Options(['--', 'help', '--project', 'literal']);
        self::assertTrue($options->direct);
        self::assertSame(['help', '--project', 'literal'], $options->arguments);
    }

    public function testMissingValueFails(): void
    {
        $this->expectException(ShortcutException::class);
        new Options(['--project']);
    }

    public function testUnknownRunnerFails(): void
    {
        $this->expectException(ShortcutException::class);
        new Options(['--runner=invalid']);
    }
}
