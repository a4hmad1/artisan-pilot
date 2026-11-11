<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Console\Command;
use ArtisanShortcuts\Console\CommandRegistry;
use ArtisanShortcuts\Console\Commands\DoctorCommand;
use ArtisanShortcuts\Console\Context;
use ArtisanShortcuts\Resolver;
use ArtisanShortcuts\ShortcutException;
use PHPUnit\Framework\TestCase;

final class CommandRegistryTest extends TestCase
{
    public function testBuiltinHandlersAndAliasReservationsStayInSync(): void
    {
        $registry = CommandRegistry::defaults();
        $expected = array_diff(Resolver::INTERNAL, ['help']);
        sort($expected);
        self::assertSame($expected, $registry->names());
        foreach ($registry->names() as $name) {
            self::assertInstanceOf(Command::class, $registry->get($name));
            self::assertStringStartsWith('pa ', $registry->get($name)->usage());
        }
    }

    public function testACommandModuleCanBeRegistered(): void
    {
        $registry = new CommandRegistry();
        $module = new class implements Command {
            public function usage(): string
            {
                return 'pa example';
            }
            public function execute(Context $context, array $arguments): int
            {
                return 37;
            }
        };
        $registry->register('example', $module);
        self::assertSame($module, $registry->get('example'));
        self::assertNull($registry->get('unregistered'));
    }

    public function testDuplicateRegistrationFails(): void
    {
        $registry = CommandRegistry::defaults();
        $this->expectException(ShortcutException::class);
        $registry->register('doctor', new DoctorCommand());
    }

    public function testShortcutCannotBeReplacedByACommandModule(): void
    {
        $this->expectException(ShortcutException::class);
        (new CommandRegistry())->register('pamf', new DoctorCommand());
    }
}
