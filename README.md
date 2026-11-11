<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/a4hmad1/artisan-pilot/main/art/logo-dark.svg">
    <img src="https://raw.githubusercontent.com/a4hmad1/artisan-pilot/main/art/logo.svg" width="344" height="96" alt="Artisan Pilot">
  </picture>
</p>

<p align="center">Your Artisan commands. A few keystrokes.</p>

<p align="center">
  <a href="https://github.com/a4hmad1/artisan-pilot/actions/workflows/ci.yml"><img src="https://github.com/a4hmad1/artisan-pilot/actions/workflows/ci.yml/badge.svg" alt="CI status"></a>
  <a href="https://github.com/a4hmad1/artisan-pilot/releases"><img src="https://img.shields.io/github/v/release/a4hmad1/artisan-pilot?color=f13c31" alt="Latest release"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-777bb4" alt="PHP 8.2 or newer"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/a4hmad1/artisan-pilot" alt="MIT license"></a>
</p>

## Introduction

Artisan Pilot gives Laravel developers short terminal commands for everyday work. Type `pas` to serve your application, `pamm` to create a model, and `patt` to run your tests. Artisan arguments, interactive prompts, output, and exit codes stay intact.

Commands are discovered from your application, so custom commands and installed Laravel packages are included. Pest, PHPUnit, and Pint have their own direct shortcuts too. This is an independently maintained Laravel community package.

```bash
pas                           # php artisan serve
pamm Post --migration         # php artisan make:model Post --migration
patt --filter=PostTest        # php artisan test --filter=PostTest
papest --parallel             # vendor/bin/pest --parallel
```

## Installation

The package requires PHP 8.2+ and Composer 2.2+. Laravel 11, 12, and 13 are integration targets; each application and test runner retains its own PHP requirements.

The standard public installation is:

```bash
composer require --dev a4hmad1/artisan-pilot
```

> **Publishing status:** Packagist registration is still pending. Until it is indexed, use the [GitHub installation](docs/usage.md#github-installation-before-packagist-registration). A GitHub release alone does not make the standard Composer command available.

Composer installs the executables in `vendor/bin`. Use `./vendor/bin/pas` immediately, or enable bare shortcuts in your current Bash session:

```bash
eval "$(./vendor/bin/pa shell bash)"
```

For Zsh, replace `bash` with `zsh`. [Fish, PowerShell, PATH, and global installation](docs/usage.md#enable-bare-commands-such-as-pas) are documented separately. Shell activation does not modify your profile. Regenerate it when commands or aliases change.

## Shortcuts

| Shortcut | Command |
| --- | --- |
| `pas` | `php artisan serve` |
| `pat` | `php artisan tinker` |
| `pad` | `php artisan down` |
| `paup` | `php artisan up` |
| `pam` | `php artisan migrate` |
| `pamf` | `php artisan migrate:fresh` |
| `pamm` | `php artisan make:model` |
| `pamc` | `php artisan make:controller` |
| `parl` | `php artisan route:list` |
| `paoc` | `php artisan optimize:clear` |
| `patt` | `php artisan test` |
| `papest` | `vendor/bin/pest` |
| `papu` | `vendor/bin/phpunit` |
| `papint` | `vendor/bin/pint` |

`pat` keeps its original Tinker meaning. `patt` runs Laravel's test command. Test and tool shortcuts require those dependencies in your application; Pilot does not install them or change which testing framework you use.

## Testing with Pest or PHPUnit

`patt` delegates to Laravel's test command, which runs the application's installed test runner. Use `papest` or `papu` when you want to invoke that PHP Composer binary directly.

```bash
patt
patt --filter=PostTest
patt --parallel --processes=2
papest --filter=PostTest
papest --parallel --processes=2
papu --testsuite=Unit
papint --test
```

Parallel testing requires the application's compatible ParaTest dependencies. Coverage requires an appropriate coverage driver. All runner flags are passed through. Read the [testing guide](docs/testing.md) for dependencies, supported entry points, and verified versions.

## Your application's full command set

```bash
./vendor/bin/pa commands
./vendor/bin/pa commands make --json
./vendor/bin/pa shortcuts
./vendor/bin/pa vendor:publish --help
```

Dynamic shortcuts use `pa` plus the initials of each colon, hyphen, or underscore-separated word. `queue:work` becomes `paqw`; `pail` becomes `pap`. Shell activation exposes unambiguous names directly. Fixed shortcuts keep their meanings, and ambiguous names stop with candidates instead of selecting a command.

Every command currently exposed by the fixture applications is listed in the [command audit](docs/command-support.md). The report distinguishes exact/help verification from actual execution of command handlers.

## Custom shortcuts

```bash
./vendor/bin/pa alias:set models make:model Post --migration
./vendor/bin/pa alias:set palogs pail --filter=ERROR
./vendor/bin/pa alias:list
```

Refresh shell activation, then run `models --factory` or `palogs`. Aliases are stored in `.artisan-shortcuts.json`, with validated argument arrays and locked, atomic writes. [Alias management and collision rules](docs/usage.md#custom-shortcuts) cover replacement, removal, reserved names, and team configuration.

## Sail, Docker, and additional tools

```bash
pas --runner=sail --host=0.0.0.0
patt --runner=sail --parallel --processes=2
papest --runner=sail --filter=PostTest
pam --runner=docker --container=my-app
./vendor/bin/pa bin phpstan analyse
```

Runners are selected explicitly. `pa bin NAME` runs installed **PHP** Composer tools and honors local Composer bin-directory configuration. It does not execute arbitrary shell templates. [Runner configuration](docs/usage.md#sail-docker-and-project-options) describes project paths, container requirements, and Windows/WSL behavior.

## Documentation

- [Usage and installation](docs/usage.md)
- [Pest, PHPUnit, and parallel testing](docs/testing.md)
- [Complete discovered command audit](docs/command-support.md)
- [Verification evidence and platform limits](docs/verification.md)
- [Upgrading from Artisan Shortcuts](docs/migration.md)
- [GitHub and Packagist publishing](docs/publishing.md)

The package never adds `--force`, suppresses Laravel's confirmations, or changes your shell profile automatically. Commands keep Laravel's own destructive-operation policy. Only use trusted applications and shared alias files.

## Contributing

Please read the [contribution guide](CONTRIBUTING.md). Runtime changes should include behavior-focused tests and pass the project's formatting, static-analysis, and integration checks.

## Code of Conduct

Help keep discussions respectful and useful. See the [Code of Conduct](CODE_OF_CONDUCT.md).

## Security Vulnerabilities

Please follow the [security policy](SECURITY.md) when reporting a vulnerability. Do not include credentials, application secrets, or private data in public issues.

## License

Artisan Pilot is open-source software licensed under the [MIT license](LICENSE).
