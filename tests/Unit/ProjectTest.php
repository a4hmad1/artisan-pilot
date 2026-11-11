<?php

declare(strict_types=1);

namespace ArtisanShortcuts\Tests\Unit;

use ArtisanShortcuts\Project;
use ArtisanShortcuts\ShortcutException;
use ArtisanShortcuts\Tests\TestCase;

final class ProjectTest extends TestCase
{
    public function testFindsNearestArtisanFromNestedDirectory(): void
    {
        file_put_contents($this->directory . '/artisan', '<?php');
        mkdir($this->directory . '/nested/deep', 0700, true);
        self::assertSame(realpath($this->directory), Project::find($this->directory . '/nested/deep'));
        file_put_contents($this->directory . '/nested/artisan', '<?php');
        self::assertSame(realpath($this->directory . '/nested'), Project::find($this->directory . '/nested/deep'));
    }

    public function testInvalidDirectoryFails(): void
    {
        $this->expectException(ShortcutException::class);
        Project::find($this->directory . '/missing');
    }

    public function testMissingProjectFails(): void
    {
        $this->expectException(ShortcutException::class);
        Project::find($this->directory);
    }
}
