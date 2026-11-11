<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Integration;

use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CliTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        copy(dirname(__DIR__) . '/Fixtures/artisan', $this->directory . '/artisan');
    }

    public static function shortcuts(): array
    {
        return array_map(
            static fn ($alias, $command) => [$alias, $command],
            array_keys(Resolver::DEFAULTS),
            array_values(Resolver::DEFAULTS)
        );
    }

    #[DataProvider('shortcuts')]
    public function testExecutableForEveryRequestedShortcut(string $alias, string $command): void
    {
        [$status, $output, $errors] = $this->cli(['a b', '--option=two words'], $alias);
        self::assertSame(0, $status, $errors);
        self::assertSame([$command, 'a b', '--option=two words'], json_decode($output, true));
    }

    public function testArgumentsAreLiteralAndNeverShellEvaluated(): void
    {
        $args = [
            'a b', '"quoted"', "'single'", '$(touch PWNED)', ';touch PWNED', '& echo bad',
            '%PATH%', 'مرحبا', '', '--', '--runner=docker',
        ];
        [$status, $output, $errors] = $this->cli(['probe:args', ...$args]);
        self::assertSame(0, $status, $errors);
        self::assertSame(['probe:args', ...$args], json_decode($output, true));
        self::assertFileDoesNotExist($this->directory . '/PWNED');
    }

    public function testStdinStdoutStderrAndExitCodes(): void
    {
        [$status, $output, $errors] = $this->cli(['probe:input'], input: "input to terminal\n");
        self::assertSame(0, $status);
        self::assertSame("input to terminal\n", $output);
        self::assertSame("separate stderr\n", $errors);
        self::assertSame(42, $this->cli(['probe:exit'])[0]);
    }

    public function testConfirmationIsNotBypassed(): void
    {
        [$status, $output] = $this->cli(['probe:confirm'], input: "n\n");
        self::assertSame(1, $status);
        self::assertStringContainsString('cancelled', $output);
        self::assertSame(0, $this->cli(['probe:confirm'], input: "y\n")[0]);
    }

    public function testCollisionStopsAndCustomAliasResolvesIt(): void
    {
        [$status, $output, $errors] = $this->cli(['paqw']);
        self::assertSame(2, $status);
        self::assertSame('', $output);
        self::assertStringContainsString('queue:watch, queue:work', $errors);
        self::assertSame(0, $this->cli(['alias:set', 'paqw', 'queue:work', '--once'])[0]);
        [$status, $output] = $this->cli(['paqw', '--queue=mail']);
        self::assertSame(0, $status);
        self::assertSame(['queue:work', '--once', '--queue=mail'], json_decode($output, true));
        self::assertSame(0, $this->cli(['alias:remove', 'paqw'])[0]);
        self::assertSame(2, $this->cli(['paqw'])[0]);
    }

    public function testHelpVersionDiagnosticsAndDiscovery(): void
    {
        self::assertStringContainsString('Usage:', $this->cli(['--help'])[1]);
        self::assertMatchesRegularExpression(
            '/^Artisan Pilot [0-9]+\.[0-9]+\.[0-9]+(?:-[a-zA-Z0-9.]+)?\R$/',
            $this->cli(['--version'])[1]
        );
        self::assertStringContainsString('Discovery: OK', $this->cli(['doctor'])[1]);
        $output = $this->cli(['shortcuts'])[1];
        self::assertStringContainsString('[AMBIGUOUS]', $output);
        self::assertStringContainsString('plugin:task', $output);
        self::assertSame(['plugin:task'], json_decode($this->cli(['papt'])[1], true));
    }

    public function testRawModeAvoidsBrokenDiscoveryAndManagementNames(): void
    {
        file_put_contents($this->directory . '/artisan', '<?php echo json_encode(array_slice($argv,1));');
        self::assertSame(
            ['doctor', '--env=testing'],
            json_decode($this->cli(['--', 'doctor', '--env=testing'])[1], true)
        );
        self::assertSame(2, $this->cli(['doctor'])[0]);
    }

    public function testProjectOptionAndSubdirectories(): void
    {
        mkdir($this->directory . '/space directory');
        self::assertSame(
            0,
            $this->cli(['--project', $this->directory, 'probe:args'], cwd: $this->directory . '/space directory')[0]
        );
        self::assertSame(0, $this->cli(['probe:args'], cwd: $this->directory . '/space directory')[0]);
    }

    public function testAliasListIsUsableWithoutBootingApplication(): void
    {
        file_put_contents($this->directory . '/artisan', '<?php exit(99);');
        self::assertSame([0, "{}\n", ''], $this->cli(['alias:list']));
    }
    public function testPortableFallbackWithoutPcntl(): void
    {
        $options = ['-d', 'disable_functions=pcntl_exec'];
        [$status, $output, $error] = $this->cli(
            ['probe:input'],
            input: "fallback input\n",
            phpOptions: $options
        );
        self::assertSame(0, $status);
        self::assertSame("fallback input\n", $output);
        self::assertSame("separate stderr\n", $error);
        self::assertSame(42, $this->cli(['probe:exit'], phpOptions: $options)[0]);
    }

    public function testConcurrentAliasUpdatesDoNotLoseEntries(): void
    {
        $children = [];
        for ($number = 0; $number < 8; $number++) {
            $process = proc_open(
                [PHP_BINARY, dirname(__DIR__, 2) . '/bin/pa', 'alias:set', 'alias' . $number, 'probe:args'],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                $this->directory,
                null,
                ['bypass_shell' => true]
            );
            self::assertIsResource($process);
            fclose($pipes[0]);
            $children[] = [$process, $pipes];
        }
        foreach ($children as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertSame(0, proc_close($process), $error . $output);
        }
        $aliases = json_decode($this->cli(['alias:list'])[1], true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(8, $aliases);
        for ($number = 0; $number < 8; $number++) {
            self::assertSame(['probe:args'], $aliases['alias' . $number]);
        }
    }
}
