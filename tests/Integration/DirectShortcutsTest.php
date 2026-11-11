<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Integration;

use ArtisanShortcuts\Process;
use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ShellIntegration;
use ArtisanShortcuts\Tests\TestCase;

#[\PHPUnit\Framework\Attributes\Group('unix-shell')]
final class DirectShortcutsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('POSIX shell execution is tested on Unix; use PowerShell on native Windows.');
        }
        copy(dirname(__DIR__) . '/Fixtures/artisan', $this->directory . '/artisan');
    }

    public function testEveryFixedCommandWorksBareWithNoPilotOrPaPrefix(): void
    {
        [$status, $script, $error] = $this->cli(['shell', 'bash']);
        self::assertSame(0, $status, $error);
        foreach (Resolver::DEFAULTS as $name => $command) {
            $path = $this->directory . '/run-' . $name . '.sh';
            file_put_contents($path, $script . "\n{$name} 'literal space' '--option=value'\n");
            $output = (new Process())->capture(['bash', '--noprofile', '--norc', $path], $this->directory);
            self::assertSame([$command, 'literal space', '--option=value'], json_decode($output, true));
        }
    }

    public function testBareDynamicAndCustomAliasesPreserveLiteralArgumentsAndExitStatus(): void
    {
        self::assertSame(0, $this->cli(['alias:set', 'models', 'make:model', '--migration'])[0]);
        [$status, $script, $error] = $this->cli(['shell', 'bash']);
        self::assertSame(0, $status, $error);
        $path = $this->directory . '/run.sh';
        file_put_contents($path, $script . "\nmodels 'A B' '\$(touch PWNED)'\n");
        $output = (new Process())->capture(['bash', '--noprofile', '--norc', $path], $this->directory);
        self::assertSame(['make:model', '--migration', 'A B', '$(touch PWNED)'], json_decode($output, true));
        self::assertFileDoesNotExist($this->directory . '/PWNED');
        file_put_contents($path, $script . "\npap --filter=ERROR\n");
        $output = (new Process())->capture(['bash', '--noprofile', '--norc', $path], $this->directory);
        self::assertSame(['pail', '--filter=ERROR'], json_decode($output, true));
        file_put_contents($path, $script . "\npape\nresult=\$?\nprintf '%s' \"\$result\"\n");
        self::assertSame('42', (new Process())->capture(['bash', '--noprofile', '--norc', $path], $this->directory));
    }

    public function testQuotedEntrypointPathsCannotEscapeTheGeneratedFunction(): void
    {
        $path = $this->directory . "/a 'quote;\$(touch PWNED).php";
        file_put_contents($path, '<?php echo json_encode(array_slice($argv, 1));');
        $script = (new ShellIntegration())->render('bash', ['pas'], $path, PHP_BINARY);
        $run = $this->directory . '/quoted.sh';
        file_put_contents($run, $script . "\npas 'literal space'\n");
        $output = (new Process())->capture(['bash', '--noprofile', '--norc', $run], $this->directory);
        self::assertSame(['--shortcut=pas', 'literal space'], json_decode($output, true));
        self::assertFileDoesNotExist($this->directory . '/PWNED');
    }

    public function testNamedShortcutModeRetainsWrapperOptions(): void
    {
        [$status, $output, $error] = $this->cli([
            '--shortcut=pamm', '--project', $this->directory, 'Post', '--migration',
        ]);
        self::assertSame(0, $status, $error);
        self::assertSame(['make:model', 'Post', '--migration'], json_decode($output, true));
        self::assertSame(2, $this->cli(['--shortcut=x;bad'])[0]);
    }
}
