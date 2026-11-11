# Artisan Shortcuts

[![CI](https://github.com/a4hmad1/Shortcut-arsitan---laravel-/actions/workflows/ci.yml/badge.svg)](https://github.com/a4hmad1/Shortcut-arsitan---laravel-/actions)

Short commands. Real Artisan. Your application's commands, arguments, prompts, and exit codes.

A PHP 8.2+ Composer CLI package for Laravel 11, 12, and 13. It discovers commands from the application you are working in, including custom commands and installed packages. It has **no runtime framework dependencies**, service provider, Composer plugin, or automatic shell-profile changes.

## Install

**Available immediately from this GitHub repository**, before Packagist registration:

```bash
composer config repositories.artisan-shortcuts vcs https://github.com/a4hmad1/Shortcut-arsitan---laravel-.git
composer require --dev a4hmad1/artisan-shortcuts:dev-main
./vendor/bin/pa doctor
./vendor/bin/pamm Post --migration
```

After the maintainer submits the repository to Packagist and publishes a stable tag:

```bash
composer require --dev a4hmad1/artisan-shortcuts
```

Local Composer installation creates executables in `vendor/bin`. Run `./vendor/bin/pas`, or add **the project's absolute vendor/bin directory** to PATH. Composer does not put project binaries on your shell's PATH automatically. Do not add a relative `vendor/bin` to your permanent PATH.

For commands available across projects, install globally. Until Packagist registration:

```bash
composer global config repositories.artisan-shortcuts vcs https://github.com/a4hmad1/Shortcut-arsitan---laravel-.git
composer global require a4hmad1/artisan-shortcuts:dev-main
composer global config bin-dir --absolute
```

Add the directory printed by the last command to PATH using your shell/OS settings. After Packagist registration and a stable release, use `composer global require a4hmad1/artisan-shortcuts`.

Run inside any trusted Laravel project, including a subdirectory:

```bash
pas --host=127.0.0.1 --port=8001
pamm Post --migration
parl --path=api
pa queue:work --once
pa shortcuts
```

On Windows, Composer generates `.bat` proxies: `vendor\bin\pamm Post --migration` or `pamm` when the global bin directory is on PATH. PowerShell local invocation uses `.\vendor\bin\pamm`. Sail runs inside WSL.

## Fixed shortcuts

| Executable | Artisan command |
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

These are also accepted as arguments: `pa pamm Post`. Missing commands are reported clearly; for example, `pat` requires Tinker in the application.

## Every command, discovered dynamically

`pa shortcuts` runs the application's `artisan list --format=json --no-ansi --no-interaction` through the selected runner. No command cache can become stale when you install a package or add a command.

The generated abbreviation is `pa` plus the first character of each word separated by `:`, `-`, or `_`:

```text
queue:work             paqw
make:middleware        pamm       (fixed pamm means make:model)
vendor:sync-orders     pavso
```

Run a generated shortcut with **`pa SHORTCUT`**, for example `pa pavso`. Generated and custom aliases are resolved by `pa`; they do not create additional shell executables. Only `pa` and the ten fixed shortcuts are Composer binaries.

Resolution is deterministic:

1. Fixed shortcuts retain their documented meanings.
2. Exact Artisan command names retain their meanings.
3. Project aliases take precedence over generated abbreviations.
4. A generated abbreviation executes only if exactly one command matches.

If `queue:work` and `queue:watch` both exist, `pa paqw` fails with both candidates. Use `pa queue:work` or explicitly choose a custom alias. Nothing is selected silently. `pa shortcuts` marks ambiguous generated mappings; fixed shortcuts are displayed with their fixed targets.

The wrapper reserves `help`, `shortcuts`, `doctor`, and `alias:*` management actions. Access Artisan commands with those names through raw mode:

```bash
pa -- help migrate
pa -- list
pa -- some:hidden-command --help
```

Raw mode passes every following token directly to Artisan without discovery or alias resolution. It also provides access to hidden commands that Artisan omits from its JSON list and an escape hatch when application boot output corrupts JSON.

## Your own aliases

```bash
pa alias:set models make:model Post --migration
pa models --factory
pa alias:set models make:model Article --migration
pa alias:list
pa alias:remove models

# Explicitly resolve an ambiguous generated abbreviation:
pa alias:set paqw queue:work --once
pa paqw --queue=mail
```

`alias:set` creates or replaces an alias. The target must be an exact, currently discovered Artisan command. Arguments are stored as a JSON array, never parsed as shell code. Invocation arguments are appended to stored arguments. These are direct command mappings, not recursive aliases or shell scripts.

Aliases live in the project's `.artisan-shortcuts.json`. Writes use an exclusive sidecar lock and atomic file replacement. Names are lowercase letters/digits/`_`/`-`, start with a letter, and have a 64-character limit. Fixed shortcuts, exact command names, and wrapper management names are reserved. If a new package later introduces an exact command matching an existing custom alias, the exact command wins. A missing alias target produces an error. Invalid configuration is never silently replaced.

`alias:list` and `alias:remove` work even when application boot is broken. Add `.artisan-shortcuts.json` and `.artisan-shortcuts.json.lock` to your application's `.gitignore` for personal aliases; commit the JSON file if your team wants shared aliases. Do not store secrets in aliases.

## Wrapper options and environments

Place wrapper options **before** the command or before the first Artisan argument for a fixed executable. Everything after that point belongs to Artisan.

```bash
pa --project /path/to/app --php /usr/bin/php8.3 route:list --json
pamm --project /path/to/app Invoice --migration
pa --runner=sail queue:work --once
pam --runner=sail --no-interaction
```

| Option | Default | Purpose |
| --- | --- | --- |
| `--project PATH` | Current directory | Find the nearest ancestor with an `artisan` file |
| `--php PATH` | Current PHP executable | Local runner PHP executable, one path/name, no embedded flags |
| `--runner local\|sail\|docker` | `local` | Explicit execution environment |
| `--container NAME` | None | Docker container name/ID |
| `--container-workdir PATH` | `/var/www/html` | Laravel project directory inside Docker |

There is no automatic switch to Sail just because Sail is installed. Discovery and execution always use the same runner. Both inherit the current process environment. Environment-specific command registration should use the same `APP_ENV` for discovery and execution; raw mode can forward `--env` directly without discovery.

### Docker and Sail

With Sail already running:

```bash
pa --runner=sail shortcuts
pas --runner=sail --host=0.0.0.0
```

Sail requires Bash, Docker, and the application's `vendor/bin/sail`. On Windows use WSL. Start containers yourself; the wrapper does not manage container lifecycles.

For an existing Docker container:

```bash
pa --runner=docker --container=my-laravel-app --container-workdir=/var/www/html shortcuts
pam --runner=docker --container=my-laravel-app --no-interaction
```

The local project still needs an `artisan` file for project selection and alias storage. The application and dependencies must also exist at the container workdir. Docker invocation uses `docker exec`, keeps stdin open for execution, and adds a TTY only when local stdin and stdout are terminals. Discovery does not allocate a TTY. `--php` affects only the local runner; Docker uses `php` inside the container and Sail controls its own PHP.

## Safety and terminal behavior

Arguments go to argument-array process APIs with shell bypass enabled. Shell metacharacters are literal arguments. The package never adds `--force`, `--no-interaction`, or any other options to the **executed command**. Discovery alone is noninteractive.

Destructive commands retain exactly the confirmations Laravel or their package implements. Commands such as `migrate:fresh` can destroy development data without a Laravel confirmation; a shortcut does not add a second safety system. Custom aliases may explicitly include options such as `--force`; review shared alias files before use.

On Unix with PCNTL, execution replaces the wrapper process, preserving terminal input/output, signals, and exit status. Without PCNTL, including native Windows, a child inherits standard streams and its normal exit code is returned. Signal behavior on that fallback follows PHP/OS process semantics. No execution timeout is imposed. Interactive Tinker and long-running queue workers retain the terminal. Discovery is bounded to 30 seconds and 16 MiB of output.

Discovery boots the application once, followed by execution in a fresh process. Application boot code can have its own side effects and must not print text before Artisan's JSON. Run only trusted applications. Failures in wrapper usage/discovery/alias resolution return `2`; unexpected wrapper errors return `1`; executed Artisan command exit codes pass through unchanged.

## Troubleshooting

```bash
pa --help
pa doctor
pa shortcuts
pa -- list --format=json
```

- **Command not found in the shell:** use `./vendor/bin/pa` locally, or add Composer's absolute global bin directory to PATH.
- **No Artisan file:** run inside the application or select `--project`.
- **Discovery failure:** run raw `pa -- list --format=json` and fix the reported application/bootstrap error. Check the selected PHP and container.
- **Ambiguity:** use the full command name or set an explicit custom alias.
- **Malformed alias JSON:** repair `.artisan-shortcuts.json`; commands will not silently ignore broken configuration. Raw mode still works.
- **Sail missing:** install/configure Sail in the application, or choose the local runner.

## Compatibility and development

The package itself requires PHP 8.2+ and Composer 2.2+. Laravel 13 applications require PHP 8.3+. Laravel 11/12/13 are integration targets; future versions can work as long as their Artisan JSON and process interface remain compatible. Future major compatibility is not guaranteed before testing. Laravel 11 is an end-of-life integration target, not a recommendation for new production applications.

```bash
composer install
composer check
php tools/prepare-fixture.php
composer install --working-dir=tests/laravel
composer test
```

Without the fixture dependencies, the real Laravel integration tests explicitly skip. CI installs a real framework and Tinker and runs those tests on its supported PHP matrix. See [contributing](CONTRIBUTING.md), [architecture](docs/architecture.md), [verification evidence](docs/verification.md), and the [publishing guide](docs/publishing.md).

## License

MIT. See [LICENSE](LICENSE).
