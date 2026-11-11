<?php

declare(strict_types=1);

namespace FixtureTests\Unit;

use PHPUnit\Framework\TestCase;

final class PilotPassingTest extends TestCase
{
    public function testPilotRunnerReceivesTheRequestedEnvironment(): void
    {
        self::assertNotSame('1', getenv('ARTISAN_SHORTCUTS_TEST_FAILURE'));
    }
}
