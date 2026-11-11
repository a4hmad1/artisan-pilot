# Pest, PHPUnit, and test commands

`php artisan test` is an Artisan command registered by Laravel's testing dependencies (normally `nunomaduro/collision`). Pest and PHPUnit are Composer executables. They are different interfaces, and the package supports both.

| Entry point | Target | Purpose |
| --- | --- | --- |
| `patt` | `php artisan test` | Let Laravel select the installed runner |
| `papest` | `vendor/bin/pest` | Invoke Pest directly |
| `papu` | `vendor/bin/phpunit` | Invoke PHPUnit directly |
| `papint` | `vendor/bin/pint` | Invoke Laravel Pint directly |
| `pa bin NAME` | Project PHP Composer binary | Run another installed PHP tool |

`pat` remains Tinker. It never changes meaning when `test` is installed. `patt` is reserved explicitly to avoid that initialism collision.

## Application dependencies

Use the testing dependencies appropriate to your application's PHP and Laravel versions. Standard Laravel applications usually include Collision and their chosen runner already. Installing this shortcut package does not install or upgrade PHPUnit, Pest, ParaTest, or Pint in your application.

Pest's current major requires a newer PHP version than this package's PHP 8.2 minimum. Older compatible Pest majors can be used in older applications. Consult [Pest installation](https://pestphp.com/docs/installation) and [Laravel testing](https://laravel.com/docs/13.x/testing) when selecting versions.

## Running tests

```bash
patt --filter=PostTest
patt --testsuite=Unit
papest --filter=PostTest
papu --testsuite=Unit
```

Place wrapper options before tool arguments. Insert `--` to pass through an option that shares a wrapper name, for example `papest -- --runner=custom`.

The target test process inherits stdin, stdout, stderr, and environment variables. Its normal exit code is returned unchanged. Arguments stay separate, including filter values with spaces. The wrapper does not add force flags, runner configuration, or hidden test options.

## Parallel execution

```bash
patt --parallel --processes=2
papest --parallel --processes=2
```

The application must have a compatible ParaTest setup. The fixtures verify parallel execution with two processes; this is runner verification, not a performance benchmark. Database isolation and test cleanup remain the application's responsibility.

## Coverage and runner-specific features

Coverage flags are forwarded unchanged. An Xdebug or PCOV driver and any additional runner dependencies must be installed. Coverage collection, Pest browser testing, mutation testing, and arbitrary third-party Pest plugins are not claimed as verified by simple runner tests.

## PHP Composer tools

`pa bin NAME` and the direct tool shortcuts run PHP Composer proxies without shell interpolation. Local execution reads `config.bin-dir`, falling back to `config.vendor-dir` plus `/bin`, or `vendor/bin`. `COMPOSER_BIN_DIR` is honored when set. The selected `--php` executable launches the tool.

Sail uses its own `sail bin NAME` interface. Docker runs `php BIN_DIRECTORY/NAME` in the configured container workdir. The configured directory must exist in that environment; absolute host-only paths must be mapped or changed for the container. Non-PHP executables are deliberately rejected by the local PHP-tool launcher and should be run natively.

## Reproducing verification

```bash
php tools/prepare-test-runner.php phpunit 13
composer install --working-dir=build/runners/phpunit-13
php tools/test-runners-smoke.php phpunit 13

php tools/prepare-test-runner.php pest 13
composer install --working-dir=build/runners/pest-13
php tools/test-runners-smoke.php pest 13
```

Replace `13` with `11` or `12`. Fixtures are isolated under `build/`. The checks cover passing/failing tests, filters, native tool entry points, generic PHP-tool execution, parallel runs, and Pint validation. Detailed installed versions and results are recorded in `build/qa/runners/*/report.json` and summarized in [verification evidence](verification.md).

## Verified runner matrix

All six isolated PHPUnit/Pest fixtures passed. See the [runner version table and reports](command-support.md#test-runner-execution) for the exact versions. Passing local tests do not imply coverage-driver, browser-test, or uninstalled plugin compatibility.
