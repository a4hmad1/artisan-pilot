# Contributing

Use PHP 8.2+ and Composer 2.2+. Keep runtime dependencies minimal and command resolution deterministic. Do not add implicit force flags, recursive shell aliases, shell interpolation, or automatic shell/profile modifications.

```bash
composer install
composer check
php tools/prepare-fixture.php
composer install --working-dir=tests/laravel --no-interaction
composer test
```

For a specific real framework version:

```bash
composer update --working-dir=tests/laravel --with 'laravel/framework:^12.0' --no-interaction
composer test
```

The fixture uses SQLite in memory and `APP_ENV=local`. It registers a custom command, Tinker, Pail, and Sail. Its Composer audit security blocking is disabled **only for the isolated test fixture** to allow compatibility tests against end-of-life Laravel 11. Do not copy this policy into production projects. The distributed CLI has no Laravel dependency and does not change application audit settings.

Use PSR-12. `composer lint` checks formatting, `composer analyse` uses PHPStan level 8, and `composer test` runs PHPUnit. The root Composer lockfile is intentionally untracked for this library, so CI resolves dependencies against each PHP version. For regressions, add behavior tests for collisions, exact arguments, exit codes, or alias persistence as appropriate. Keep CLI tests independent of your personal environment and credentials.

Use `php tools/release.php` to build a source ZIP from tracked files. The ZIP includes development tooling and tests; `composer archive` uses `.gitattributes` to produce the smaller consumer distribution. Neither should contain vendor directories, credentials, generated caches, or personal aliases.

Report issues with PHP/Laravel versions, OS, installation type, selected runner, the command, expected behavior, and redacted `pa doctor` output.
