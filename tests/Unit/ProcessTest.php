<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Process;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;

final class ProcessTest extends TestCase
{
    public function testCaptureDrainsStdoutAndStderrWithoutDeadlock(): void
    {
        $code = 'for($i=0;$i<100;$i++){fwrite(STDERR,str_repeat("e",8192));echo str_repeat("x",8192);}';
        $output = (new Process())->capture([PHP_BINARY, '-r', $code], $this->directory);
        self::assertSame(819200, strlen($output));
    }

    public function testDiscoveryFailureReportsExitCodeAndStderr(): void
    {
        $this->expectException(ShortcutException::class);
        $this->expectExceptionMessage('exit 42');
        (new Process())->capture([PHP_BINARY, '-r', 'fwrite(STDERR,"bootstrap failed");exit(42);'], $this->directory);
    }

    public function testTimeout(): void
    {
        $this->expectException(ShortcutException::class);
        $this->expectExceptionMessage('timed out');
        (new Process())->capture([PHP_BINARY, '-r', 'sleep(5);'], $this->directory, 0.05);
    }
    public function testDiscoveryOutputLimit(): void
    {
        $this->expectException(ShortcutException::class);
        $this->expectExceptionMessage('exceeded 16 MiB');
        (new Process())->capture([PHP_BINARY, '-r', 'echo str_repeat("x",17*1024*1024);'], $this->directory);
    }
}
