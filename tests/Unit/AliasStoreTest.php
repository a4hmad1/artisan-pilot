<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\AliasStore;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AliasStoreTest extends TestCase
{
    public function testCreateModifyRemoveAndList(): void
    {
        $store = new AliasStore($this->directory);
        self::assertSame([], $store->all());
        $store->set('models', ['make:model', 'A B', '--migration'], ['make:model']);
        self::assertSame(['models' => ['make:model', 'A B', '--migration']], $store->all());
        $store->set('models', ['make:model', 'Other'], ['make:model']);
        self::assertSame(['models' => ['make:model', 'Other']], $store->all());
        $store->remove('models');
        self::assertSame([], $store->all());
        self::assertSame("{}\n", file_get_contents($this->directory . '/.artisan-shortcuts.json'));
    }

    public static function invalidNames(): array
    {
        return array_map(
            static fn ($name) => [$name],
            ['pas', 'help', 'doctor', 'pa', 'actual', '../escape', 'bad name', 'UPPER', 'x;touch', '']
        );
    }

    #[DataProvider('invalidNames')]
    public function testReservedAndUnsafeNamesAreRejected(string $name): void
    {
        $this->expectException(ShortcutException::class);
        (new AliasStore($this->directory))->set($name, ['actual'], ['actual']);
    }

    public static function invalidFiles(): array
    {
        return array_map(
            static fn ($json) => [$json],
            ['{', 'null', '42', '["command"]', '{"a":[]}', '{"a":"migrate"}', '{"a":[1]}', '{"a":["--force"]}']
        );
    }

    #[DataProvider('invalidFiles')]
    public function testInvalidConfigurationFailsWithoutOverwriting(string $json): void
    {
        $file = $this->directory . '/.artisan-shortcuts.json';
        file_put_contents($file, $json);
        try {
            (new AliasStore($this->directory))->set('valid', ['migrate'], ['migrate']);
            self::fail('Expected invalid config to fail');
        } catch (ShortcutException) {
            self::assertSame($json, file_get_contents($file));
        }
    }

    public function testMissingTargetFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new AliasStore($this->directory))->set('myalias', ['missing'], ['actual']);
    }

    public function testRemovingUnknownAliasFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new AliasStore($this->directory))->remove('unknown');
    }
}
