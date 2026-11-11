<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\CommandCatalog;
use ArtisanShortcuts\Invocation;
use ArtisanShortcuts\Options;
use ArtisanShortcuts\Process;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CommandCatalogTest extends TestCase
{
    public function testIncludesAliasesAndSortsAndDeduplicatesNames(): void
    {
        $json = json_encode(['commands' => [
            ['name' => 'z:task', 'aliases' => ['z', 'z:task']], ['name' => 'a:task'],
        ]], JSON_THROW_ON_ERROR);
        file_put_contents($this->directory . '/artisan', '<?php echo ' . var_export($json, true) . ';');
        $commands = (new CommandCatalog())->discover(
            new Invocation($this->directory, new Options([])),
            new Process(),
            $this->directory
        );
        self::assertSame(['a:task', 'z', 'z:task'], $commands);
    }

    public static function invalid(): array
    {
        return array_map(
            static fn ($json) => [$json],
            ['boot message{}', '{}', '{"commands":null}', '{"commands":[{}]}']
        );
    }

    #[DataProvider('invalid')]
    public function testInvalidDiscoveryFailsClearly(string $json): void
    {
        file_put_contents($this->directory . '/artisan', '<?php echo ' . var_export($json, true) . ';');
        $this->expectException(ShortcutException::class);
        (new CommandCatalog())->discover(
            new Invocation($this->directory, new Options([])),
            new Process(),
            $this->directory
        );
    }
}
