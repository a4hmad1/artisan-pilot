<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ShortcutException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResolverTest extends TestCase
{
    public static function defaults(): array
    {
        return array_map(
            static fn ($key, $value) => [$key, $value],
            array_keys(Resolver::DEFAULTS),
            array_values(Resolver::DEFAULTS)
        );
    }

    #[DataProvider('defaults')]
    public function testEveryRequestedShortcut(string $name, string $command): void
    {
        self::assertSame([$command], (new Resolver())->resolve($name, [$command], []));
    }

    public function testDeterministicCollisionDoesNotExecute(): void
    {
        $resolver = new Resolver();
        $commands = ['queue:work', 'queue:watch', 'queue:work'];
        self::assertSame(['paqw' => ['queue:watch', 'queue:work']], $resolver->abbreviations($commands));
        $this->expectException(ShortcutException::class);
        $this->expectExceptionMessage('Ambiguous shortcut paqw: queue:watch, queue:work');
        $resolver->resolve('paqw', $commands, []);
    }

    public function testExactCommandsFixedAliasesAndCustomPrecedence(): void
    {
        $resolver = new Resolver();
        $commands = ['queue:work', 'queue:watch', 'migrate', 'mine', 'other'];
        self::assertSame(
            ['queue:work', '--once'],
            $resolver->resolve('paqw', $commands, ['paqw' => ['queue:work', '--once']])
        );
        self::assertSame(['mine'], $resolver->resolve('mine', $commands, ['mine' => ['other']]));
        self::assertSame(['migrate'], $resolver->resolve('pam', $commands, ['pam' => ['other']]));
    }

    public function testGeneratedAbbreviationIncludesWordBoundaries(): void
    {
        self::assertSame(['my-plugin:sync_all'], (new Resolver())->resolve('pampsa', ['my-plugin:sync_all'], []));
    }

    public function testMissingAliasTargetFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new Resolver())->resolve('custom', ['other'], ['custom' => ['missing']]);
    }

    public function testMissingFixedTargetFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new Resolver())->resolve('pat', ['serve'], []);
    }

    public function testUnknownFails(): void
    {
        $this->expectException(ShortcutException::class);
        (new Resolver())->resolve('wrong', ['serve'], []);
    }
}
