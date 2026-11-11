# Artisan Pilot

[![CI](https://github.com/a4hmad1/artisan-pilot/actions/workflows/ci.yml/badge.svg)](https://github.com/a4hmad1/artisan-pilot/actions)

**One CLI for your Laravel application.** Run Artisan, discover package commands, use safe shortcuts, and control Sail through `pilot`.

```bash
pilot make:model Post --migration
pilot migrate
pilot pail --filter=ERROR
pilot horizon
pilot sail up -d
pilot sail test
```

Application commands come from your own Artisan registry, including custom commands and installed packages. Pail, Sail, Horizon, and other packages must already be installed when you use their commands. Pilot does not install or replace them.

Initial publication is a release candidate (`v1.0.0-rc.1`). See the verification report for tested environments and remaining hosted CI limits.

## Install now from GitHub

Inside your Laravel application:

```bash
composer config repositories.artisan-pilot vcs https://github.com/a4hmad1/artisan-pilot.git
composer require --dev a4hmad1/artisan-pilot:dev-main
./vendor/bin/pilot doctor
./vendor/bin/pilot commands
```

Local Composer executables live in `vendor/bin`. Use `./vendor/bin/pilot`, or add the **absolute** project vendor/bin directory to PATH for bare `pilot`. Composer does not automatically change your shell profile or PATH. On PowerShell use `.\vendor\bin\pilot`; Composer generates Windows `.bat` proxies.

For one installation used across projects:

```bash
composer global config repositories.artisan-pilot vcs https://github.com/a4hmad1/artisan-pilot.git
composer global require a4hmad1/artisan-pilot:dev-main
composer global config bin-dir --absolute
```

Add the printed absolute global bin directory to PATH, then run `pilot` inside any trusted Laravel application or one of its subdirectories.

After Packagist registration and a stable release tag, installation becomes:

```bash
composer require --dev a4hmad1/artisan-pilot
```

That plain Packagist command is not advertised as available before registration. The GitHub VCS installation above is available now.

## One entry point, many commands

| Goal | Command |
| --- | --- |
| List installed commands, grouped by namespace | `pilot commands` |
| Browse model/controller/etc. generators | `pilot commands make` |
| Read the registry as a JSON array | `pilot commands --json` |
| Get native Artisan command help | `pilot help make:model` |
| See Pilot module usage | `pilot help alias:set` |
| See Pilot's own help | `pilot --help` |
| List fixed, generated, and custom shortcuts | `pilot shortcuts` |
| Diagnose the current application | `pilot doctor` |
| Generate a model | `pilot make:model Post --migration` |
| List application routes | `pilot route:list --path=api` |
| Run Pail, when installed | `pilot pail --filter=ERROR` |
| Run another package's Artisan command | `pilot telescope:clear` |
| Start Sail services | `pilot sail up -d` |
| Run tests through Sail | `pilot sail test` |
| Run Artisan through Sail | `pilot --runner=sail migrate` |
| Run Pail through Sail | `pilot --runner=sail pail` |

`pilot COMMAND [ARGS...]` resolves an exact Artisan command or shortcut. `pilot sail [ARGS...]` forwards directly to the application's Sail script, so Sail's own PHP, Composer, NPM, container, and Compose commands remain available without being enumerated by Pilot. Sail startup does not boot Artisan first, so `pilot sail up` can work while the application container is stopped.

Native Artisan `list` remains available as `pilot list`. Wrapper module names such as `commands`, `doctor`, `shortcuts`, and `sail` are reserved. Use raw mode for an Artisan command with the same name, hidden commands, or when broken boot-time output prevents discovery:

```bash
pilot -- doctor
pilot -- some:hidden-command --help
pilot -- list --format=json
```

Raw mode skips discovery and alias resolution and forwards all following tokens directly to Artisan. Unknown or uninstalled package commands produce errors rather than installing packages or choosing another command.

## Keep your existing shortcuts

`pa` and all ten original executables remain available. Both entry points use the same command resolver and existing `.artisan-shortcuts.json` file.

| Executable | Equivalent Artisan command |
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

You can also run `pilot pamm Post`, or the original `pa pamm Post`.

Generated abbreviations retain their `pa` prefix: `pa` + first character of each colon, hyphen, or underscore-separated word. For example, `queue:work` generates `paqw`; run `pilot paqw`. Generated/custom aliases do not create extra executables on PATH.

Resolution order is fixed shortcuts, exact Artisan names, project aliases, then unambiguous generated abbreviations. A collision such as `queue:work`/`queue:watch` stops with both candidates. A fixed `pamm` always means `make:model`; the shortcuts screen shows any other commands whose generated abbreviations are shadowed, so you can use their full names. Missing fixed targets and stale custom targets are marked clearly.

## Your own aliases

```bash
pilot alias:set models make:model Post --migration
pilot models --factory
pilot alias:set models make:model Article --migration
pilot alias:list
pilot alias:remove models
pilot alias:set paqw queue:work --once
```

Alias targets must be exact installed Artisan commands. Stored arguments are JSON arrays, and invocation arguments are appended without shell parsing. Aliases are not recursive mappings or shell scripts.

The existing project file remains `.artisan-shortcuts.json`, so renaming this package does not erase aliases. Writes use a sidecar lock and atomic file replacement. Invalid configuration is never silently overwritten. Alias names start with a lowercase letter and contain lowercase letters, digits, `_`, or `-`, with a 64-character limit. Wrapper commands, fixed shortcuts, and exact Artisan names are reserved.

`alias:list` and `alias:remove` do not boot Laravel. Add `.artisan-shortcuts.json` and `.artisan-shortcuts.json.lock` to your application's `.gitignore` for personal aliases, or commit the JSON for team aliases. Do not put secrets in aliases. `doctor` reports missing alias targets.

## Runners and options

```bash
pilot --project /path/to/app --php /usr/bin/php8.3 route:list --json
pamm --project /path/to/app Invoice --migration
pilot --runner=sail pail
pilot --runner=docker --container=my-app --container-workdir=/var/www/html migrate
```

| Wrapper option | Default |
| --- | --- |
| `--project PATH` | Current directory; find nearest ancestor with `artisan` |
| `--php PATH` | Current PHP executable, for local Artisan only |
| `--runner local\|sail\|docker` | `local` |
| `--container NAME` | Required for the Docker runner |
| `--container-workdir PATH` | `/var/www/html` |

Put wrapper options before the command. After the command, options belong to Artisan or Sail. For a fixed executable, wrapper options go before its first Artisan argument. There is no automatic switch to Sail when it is installed.

Sail requires the application's `vendor/bin/sail`, Bash, Docker, and appropriate project configuration. On Windows use WSL. `pilot sail` runs Sail on the host; do not combine it with `--runner=docker`. `pilot --runner=sail COMMAND` instead runs `sail artisan COMMAND`. See [Laravel Sail's documentation](https://laravel.com/docs/13.x/sail).

Docker execution requires an existing container, PHP inside it, and the application/dependencies at the selected container workdir. A local `artisan` file is still needed for project selection and alias storage. Docker uses `exec`, keeps stdin open during execution, and allocates a TTY only when local stdin and stdout are terminals. Discovery never allocates a TTY.

Pail's own runtime and extension requirements still apply; Pilot does not bundle or override them. See [Pail's documentation](https://laravel.com/docs/13.x/logging#tailing-log-messages-using-pail).

## Safety and compatibility

- Argument-array process APIs preserve spaces, quotes, shell metacharacters, standard streams, and exit codes. No shell interpolation is used for execution.
- Executed commands receive no implicit `--force` or `--no-interaction`. Laravel and package confirmations remain intact. Some destructive development commands, including `migrate:fresh`, do not ask for confirmation under Laravel's own policy.
- Discovery is a separate `artisan list --format=json --no-ansi --no-interaction` process, bounded to 30 seconds and 16 MiB. File-backed capture avoids pipe deadlocks. There is no stale command cache.
- On Unix with PCNTL, execution replaces the wrapper process and retains terminal/signals. The portable fallback inherits standard streams and returns normal exit codes; signal behavior follows PHP/OS semantics.
- Application commands boot separately for discovery and execution. Use only trusted projects; boot code and shared aliases can have side effects. Use an inherited `APP_ENV` when command registration depends on environment, or raw mode for environment-specific direct execution.
- Wrapper usage/discovery errors return `2`, unexpected wrapper errors return `1`, and executed command exit codes pass through unchanged.

PHP 8.2+ and Composer 2.2+ are required by Pilot. Laravel 11, 12, and 13 are integration targets; Laravel 13 itself requires PHP 8.3+. The package has no Laravel runtime dependency or service provider. Compatible future Laravel versions may work through the CLI contract, but are not guaranteed before testing. Laravel 11 is an end-of-life compatibility target.

## Development and publishing

The CLI is modular: command handlers implement `Console\Command`, and `Console\CommandRegistry` provides explicit registration. A per-invocation context lazily discovers Artisan commands only when a handler needs them. Third-party Artisan commands need no Pilot module or hardcoded mapping.

```bash
composer install
composer check
php tools/prepare-fixture.php
composer install --working-dir=tests/laravel
composer test
php tools/release.php
```

The real Laravel fixture includes Tinker, Pail, and Sail. Integration tests explicitly skip when its dependencies are absent. See [architecture](docs/architecture.md), [verification evidence](docs/verification.md), [contributing](CONTRIBUTING.md), and [GitHub/Packagist publishing](docs/publishing.md).

This project was renamed from Artisan Shortcuts. See [migration instructions](docs/migration.md) if the old Composer package is already installed.

MIT licensed. See [LICENSE](LICENSE).
